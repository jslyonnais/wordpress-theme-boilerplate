# wordpress-theme-boilerplate
Wordpress Theme Boilerplate - Clean way to start your WP theme.

# Installing WordPress - Good practices (todo...)
## 1. Installing Wordpress
 - Download the WordPress package to your local computer from http://wordpress.org/download/.
 - Unzip the downloaded file to a folder on your local computer.
 - Setting up a local URL (ex.: dev.wp.com).
 - Run URL and setup access to MySQL.
 - *Don't forget* it's a best practices to randomly generate a table prefix without "wp"; The reason behind is to prevent "not that smart" hacker to poke your SQL for wp_* table.

## 2. Setup Envionment
 - Open my `template.wp-config.php` and grab infos under `if(stristr( $_SERVER['SERVER_NAME'], "dev" )) { # Dev` and past-it on yours.
  - Copy for each of your env and add different credentials if needed.
 - If installation set some key to `put your unique phrase here`, change them with secure encoded string.
 - *Windows Server* copy the `template.web.config` and change it to `web.config` and add working IP address to prevent outsiders accessing to your admin pannel.

## 3. Install plugin
For public purpose, I've added plugins without licences in the repo, but I strongly suggest your to go on each missing plugins and get a licence :) They are AWESOME and really NEEDED!
### Plugin list
- ACF : Advances custom fields (you should go Pro on that one!) == Give you the possibilities to add custom fields in every element you want in WP (this is probably *The number one* plugin you want!).
- Disable Comments == WP have some problems over comment and *security*. You better just completely remove them from WP and user Disqus or any other system to manage comments on a blog.
- Error Log Monitor == Which does exactly what you think it will do!
- Yoast == Manage your WordPress SEO (will be add in each page, so you can add seo description, title, share image, etc.).
- Extra : Polylang **Pro** If you need to manage multiple languages, this is a good alternative to WPML (which is nice, but really heavy).
- Extra : WP Rocket == Now we're talking! This is so far a really good way to manage and add *cache* to your server! Easy to use and setup, it will also add CDN support to make your website fly like a rocket in the stars!
