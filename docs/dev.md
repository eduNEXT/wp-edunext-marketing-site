
WP Plugin Development on Docker
==============================

- Download this docker wordpress image https://github.com/visiblevc/wordpress-starter/tree/master/example

- Enter the `example` folder and create a new folder called "plugins" and change the volumes on docker-compose.yml so they look like:
<pre>
    volumes:
      - ./data:/data
      - ./plugins/wp-edunext-marketing-site:/app/wp-content/plugins/wp-edunext-marketing-site
</pre>
- Open the plugins folder then copy this plugin there, then start the docker container with `docker-compose up`, you should now be able to open http://localhost:8080 and see your wordpress installation running.

- We need to allow this WP docker container to connect with your edx instance through the hostname `localhost`, to do that we are going to modify the hosts file by executing this command:
```bash
docker exec -it example_wordpress_1 bash -c "echo \"$(ip address show docker0 | grep -oP '(?<=inet\s)\d+(\.\d+){3}') localhost\" | sudo tee -a /etc/hosts"
```

- Now start a simple edx instance using docker-compose, it may be https://github.com/edx/devstack or a similar one.

- Open http://localhost:8080/wp-admin/options-general.php?page=wp-edunext-marketing-site_settings and login as admin by entering `root` for both user and password.

- In the field labeled "Base domain for the open edX domain" enter the URL of your edx instance, which is likely to be `http://localhost:18000` then click save.

- Go to tab "Navigation Menu Settings" find the field labeled "Name of the shared cookie that signals an open session" and write `edxloggedin` (or whatever you are using), then on the field named "Name of the shared cookie that holds the user info" write `edx-user-info` (or whatever you are using)

<div align="center"><img src="https://pbs.twimg.com/media/B4kIaZHCUAAKB6N.png" width="300" /></div>