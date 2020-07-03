# OS2Web Borger.dk Drupal module

## Module purpose

The aim of this module is to provide integration with Borger.dk Content (https://www.borger.dk), and expose this content to be used in Drupal.

## How does it work

Borger.dk content is being imported from Borger.dk SOAP webservice using an unofficial PHP Borger.dk library (https://github.com/ffwagency/borgerdk-php).

Since the library is not compatible with Drupal 8 and also misses important functionality (like importing the English language articles), it is substituted with a fork (https://github.com/bellcom/borgerdk-php) during install process.

Synchronization is handled via Migrate API, which is part of the Drupal 8 core functionality.

After the content is being imported, it can be references from any fieldable entity by using field of type **os2web_borgerdk_article_reference**, which is provided by this module.

The Migrate API will also take care of updating the changed Borger.dk content, as well as deleting the obsolete Borger.dk content (the content that is present in the installation but is no longer present in Borger.dk).
Before deleting the obsolete content, a notify email will be sent to the email provided in the Borger.dk settings page ```admin/config/content/os2web-borgerdk```.

## Additional settings
Settings are available under ```admin/config/content/os2web-borgerdk```
* **Fetch content for selected municipality** - Specify a municipality if the imported content needs to be municipality specific. That will be an option passed to Borger.dk SOAP webservice.
* **Send notification about obsolete Borger.dk articles** - Enable the obsolete articles notify email.
* **Recipient(s) of the email** - CSV list of the recipient of the notification email.
* **Email subject** - Subject of the email.
* **Email body** - Body of the email.

## Install

Module is available to download via composer.
```
composer require os2web/os2web_borgerdk
drush en os2web_borgerdk
```

## Import process

The import process can be done in two ways:
* Via Drush (recommended)
    * Use the following Drush command to start the migration:
        ```
        drush migrate:import os2web_borgerdk_articles_import
        ```
        Read more about the Drush commands for Migrate API on [Migrate tools](https://www.drupal.org/project/migrate_tool).
    * It is highly recommended to set up a cronjob on your server to do the run this command often

* Via Admin UI
    * Go to ```admin/structure/migrate/manage/os2web_borgerdk/migrations``` on your installation
    * Click ```Execute```
    * Click ```Execute``` on the next page as well (doing that will use default options).


## Update
Updating process for OS2Web Borger.dk module is similar to usual Drupal 8 module.
Use Composer's built-in command for listing packages that have updates available:

```
composer outdated os2web/os2web_borgerdk
```

## Automated testing and code quality
See [OS2Web testing and CI information](https://github.com/OS2Web/docs#testing-and-ci)

## Contribution

Project is opened for new features and os course bugfixes.
If you have any suggestion or you found a bug in project, you are very welcome
to create an issue in github repository issue tracker.
For issue description there is expected that you will provide clear and
sufficient information about your feature request or bug report.

### Code review policy
See [OS2Web code review policy](https://github.com/OS2Web/docs#code-review)

### Git name convention
See [OS2Web git name convention](https://github.com/OS2Web/docs#git-guideline)
