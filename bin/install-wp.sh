#!/usr/bin/env sh
set -e

# Runs automatically on `docker compose up` via the wp-cli service.
# Idempotent: safe to re-run.

cd /var/www/html

# Install WordPress (only the first time).
if wp core is-installed --allow-root >/dev/null 2>&1; then
  echo "WordPress already installed, skipping core install."
else
  echo "Installing WordPress..."
  wp core install \
    --allow-root \
    --title="Damn Vulnerable WordPress" \
    --admin_user="admin" \
    --admin_password="admin" \
    --admin_email="admin@example.com" \
    --url="http://127.0.0.1:31337/" \
    --skip-email

  # Seed an "editor" user and the "Hack Me If You Can" post.
  wp user create editor editor@yourdomain.com \
    --role=editor \
    --user_pass=editor \
    --display_name=Editor \
    --allow-root
  wp post update 1 \
    --post_title="Hack Me If You Can" \
    --post_name="hack-me-if-you-can" \
    --post_content="Welcome to Damn Vulnerable WordPress. This is your first post. Edit or delete it, then start writing!" \
    --allow-root

  # Set the default theme. Only done on the initial install so theme choices
  # made in wp-admin afterwards are never clobbered by a re-run.
  if [ -d "/var/www/html/wp-content/themes/twentytwentyfive" ]; then
    echo "Activating default theme: twentytwentyfive"
    wp theme activate --allow-root twentytwentyfive || true
  elif [ -d "/var/www/html/wp-content/themes/shapely" ]; then
    echo "Activating fallback theme: shapely"
    wp theme activate --allow-root shapely || true
  fi
  echo "Demo data imported."
fi

# Install/activate a plugin via wp-cli if it was not pre-downloaded,
# otherwise just activate it. Activation is idempotent.
activate_plugin() {
  slug=$1
  ver=$2
  if [ ! -d "/var/www/html/wp-content/plugins/$slug" ]; then
    echo "Downloading and activating plugin: $slug $ver (wp-cli)"
    wp plugin install --allow-root "$slug" --version="$ver" --activate --force || {
      echo "WARNING: could not install $slug $ver"
    }
  else
    echo "Activating plugin: $slug"
    wp plugin activate --allow-root "$slug" || {
      echo "WARNING: could not activate $slug"
    }
  fi
}

activate_plugin iwp-client 1.9.4.4
activate_plugin social-warfare 3.5.2
activate_plugin wp-advanced-search 3.3.3
activate_plugin wp-file-upload 4.12.2
activate_plugin wp-file-manager 6.0
activate_plugin duplicator 1.3.26
activate_plugin custom-content-type-manager 0.9.8.1
activate_plugin wpdiscuz 7.0.4
activate_plugin simple-file-list 4.2.2
activate_plugin email-subscribers 4.2.2
# wp-time-capsule    1.21.15   # authentication bypass (CVE-2020-8771) - activation causes errors, left disabled

# Simple File List (CVE-2020-36847) uploads into wp-content/uploads/simple-file-list/.
# Create the tree up-front and hand it to the Apache user so the unauthenticated
# upload endpoint works immediately. Idempotent.
if [ ! -d "/var/www/html/wp-content/uploads" ]; then
  mkdir -p /var/www/html/wp-content/uploads/simple-file-list
  chown -R www-data:www-data /var/www/html/wp-content/uploads
  echo "Created wp-content/uploads for Simple File List."
fi

# Email Subscribers (CVE-2019-19985): the unauthenticated exporter dumps rows
# from the plugin's own tables (wp_ig_*). Seed 3 contacts so the CSV is
# populated out of the box. Guarded: only runs when the table is empty.
wp eval '
  global $wpdb;
  $t = $wpdb->prefix . "ig_contacts";
  if ( (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t" ) === 0 ) {
    $wpdb->insert( $wpdb->prefix . "ig_lists",
      array( "slug" => "newsletter", "name" => "Newsletter" ) );
    $list_id = (int) $wpdb->insert_id;
    foreach ( array( "Alice", "Bob", "Eve" ) as $first ) {
      $email = strtolower( $first ) . "@example.com";
      $wpdb->insert( $t,
        array(
          "email" => $email, "first_name" => $first, "last_name" => "Example",
          "source" => "widget", "status" => "subscribed", "hash" => md5( $email ),
          "created_at" => current_time( "mysql" ), "updated_at" => current_time( "mysql" ),
        ) );
      $wpdb->insert( $wpdb->prefix . "ig_lists_contacts",
        array(
          "list_id" => $list_id, "contact_id" => (int) $wpdb->insert_id,
          "status" => "subscribed", "optin_type" => 1,
          "subscribed_at" => current_time( "mysql" ),
        ) );
    }
    echo "Seeded Email Subscribers contacts.\n";
  }
' --allow-root || echo "WARNING: could not seed Email Subscribers contacts (non-fatal)"

echo "DVWP setup finished."