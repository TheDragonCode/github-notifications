# GitHub Notifications

![The Dragon Code: GitHub Notifications](https://preview.dragon-code.pro/the-dragon-code/github-notifications.svg?brand=laravel)

> GitHub Notifications was created by, and is maintained by `The Dragon Code`,
> and is a simple command line tool to mark all notifications about issues or rejected PRs as read on a given
> organization.

## Installation

PHP 8.2+ is required. To get the latest version, simply require the project using [Composer](https://getcomposer.org):

```Bash
composer global require dragon-code/github-notifications:*
```

Alternatively, you can simply clone the repo and run `composer install` in the folder.

## Authentication

You'll also need to create yourself a
[personal access token](https://github.com/settings/tokens/new?description=Notifications%20Reader)
for GitHub's API with access to the `notifications` scope.

## Usage

By default, we'll try and read your personal access token for GitHub from the `GITHUB_TOKEN` environment variable,
however you can also specify a token with the `--token` command-line flag.

To read all issue notifications:

```Bash
notifications read
```

To clear all issue notifications for the Laravel organization:

```Bash
notifications read laravel
```

Or, if you are specifying a token:

```Bash
notifications read laravel --token {...}
```

In addition to the organization, you can also specify the full or partial name of the repository. For example:

```Bash
notifications read some/name
# or
notifications read some/na
# or
notifications read so
```

При определении имени используется функция [`str_starts_with`](https://www.php.net/manual/en/function.str-starts-with).

### Options

By default, only those Issues and Pull Requests that have been closed or merged are marked as read.

But you can define the parameters yourself:

```Bash
-i, --without-issues  Exclude issues from processing
-s, --without-pulls   Exclude Pull Requests from processing
-o, --with-open       Process including open Issues and Pull Requests
-n, --no-interaction  Do not ask any interactive question
-q, --quiet           Do not output any message
```

For example:

```Bash
# without issues + with open
notifications read qwerty -ion
```

With this set of options, notifications that have:

- whose repository name begins with the word `qwerty`
- Pull Requests only, both open and closed
- will not be asked to continue in the console

> You can call the console command to display help information:
>
> ```bash
> notifications read --help
> ```

## Support Us

❤️ The Dragon Code? Please consider supporting our collective on [Boosty](https://boosty.to/dragon-code).

## License

This package is licensed under the [MIT License](LICENSE).
