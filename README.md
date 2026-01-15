# GitHub Notifications

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://banners.beyondco.de/Github%20Notifications.png?theme=dark&pattern=topography&style=style_2&fontSize=100px&images=https%3A%2F%2Fwww.php.net%2Fimages%2Flogos%2Fnew-php-logo.svg&packageManager=composer+require&packageName=dragon-code%2Fgithub-notifications&description=Reduce+your+notification+burden+on+GitHub%21&md=1&showWatermark=1">
    <img src="https://banners.beyondco.de/Github%20Notifications.png?theme=light&pattern=topography&style=style_2&fontSize=100px&images=https%3A%2F%2Fwww.php.net%2Fimages%2Flogos%2Fnew-php-logo.svg&packageManager=composer+require&packageName=dragon-code%2Fgithub-notifications&description=Reduce+your+notification+burden+on+GitHub%21&md=1&showWatermark=1" alt="Github Notifications">
</picture>

[![Stable Version][badge_stable]][link_packagist]
[![Total Downloads][badge_downloads]][link_packagist]
[![Github Workflow Status][badge_build]][link_build]
[![License][badge_license]][link_license]

> GitHub Notifications was created by, and is maintained by `The Dragon Code`,
> and is a simple command line tool to mark all notifications about issues or rejected PRs as read on a given
> organization.

## Installation

PHP 8.2+ is required. To get the latest version, simply require the project using [Composer](https://getcomposer.org):

```Bash
composer global require dragon-code/github-notifications:*
```

Alternatively, you can simply clone the repo and run `composer install` in the folder.

## Update global dependencies

To update global dependencies, use the console command:

```Bash
composer global update
```

## Authentication

You'll also need to create yourself a
[personal access token](https://github.com/settings/tokens/new?description=Notifications%20Reader)
for GitHub's API with access to the `notifications` scope.

By default, we check several places for the presence of a token in the following order:

1. The `token` parameter passed when calling the console command
2. The `GITHUB_TOKEN` environment variable
3. `~/.composer/auth.json` file
4. `~/.config/.composer/auth.json` file
5. `~/.config/composer/auth.json` file
6. `~/AppData/Roaming/Composer/auth.json` file
7. `~/composer/auth.json` file
8. `%USERPROFILE%/AppData/Roaming/Composer/auth.json` file

If the token is not found, you will receive a message about this.

## Usage

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

In addition, you can use any part of the organization name and/or repository name to check against the template:

```Bash
notifications read laravel/framework
# or
notifications read lara*/fra
# or
notifications read framework
# or
notifications read work
# or
notifications read fra*rk
```

Pattern matching is implemented using the [`Str::is`](https://laravel.com/docs/strings#method-str-is) method.

You can also specify several names:

```Bash
notifications read laravel/framework laravel/jet
```

When determining the name, the [`str_starts_with`](https://www.php.net/manual/en/function.str-starts-with) function is
used.

### Options

By default, only those Issues and Pull Requests that have been closed or merged are marked as read.

But you can define the parameters yourself:

```Bash
-r, --except-repository  Exclude repositories from processing
-i, --except-issues      Exclude issues from processing
-p, --except-pulls       Exclude Pull Requests from processing
-m, --except-mentions    Exclude notifications with your mention from processing
-o, --with-open          Process including open Issues and Pull Requests
-n, --no-interaction     Do not ask any interactive question
-q, --quiet              Do not output any message
```

For example:

```Bash
# except issues + with open
notifications read laravel -ion
```

With this set of options, notifications that have:

- whose repository name begins with the word `laravel`
- Pull Requests only, both open and closed
- will not be asked to continue in the console

> You can call the console command to display help information:
>
> ```bash
> notifications read --help
> ```

You can also exclude certain repositories:

```Bash
notifications read laravel -ion -r laravel/framework -r laravel/breeze
```

With this set of options, notifications that have:

- whose repository name begins with the word `laravel`
- Pull Requests only, both open and closed
- will not be asked to continue in the console
- repositories `laravel/framework` and `laravel/breeze` will not be processed

## Result

### Before

Execute a console command with the following parameters:

```Bash
notifications read -n --except-mentions
```

![before](.github/images/before.png)

### After

![after](.github/images/after.png)

### After with `--with-open` option

Execute a console command with the following parameters:

```Bash
notifications read -n --except-mentions --with-open
```

![after](.github/images/after-with-open.png)

## Support Us

❤️ The Dragon Code? Please consider supporting our collective on [Boosty](https://boosty.to/dragon-code).

## License

This package is licensed under the [MIT License](LICENSE).

[badge_build]:          https://img.shields.io/github/actions/workflow/status/TheDragonCode/github-notifications/tests.yml?style=flat-square

[badge_downloads]:      https://img.shields.io/packagist/dt/dragon-code/github-notifications.svg?style=flat-square

[badge_license]:        https://img.shields.io/packagist/l/dragon-code/github-notifications.svg?style=flat-square

[badge_stable]:         https://img.shields.io/github/v/release/TheDragonCode/github-notifications?label=stable&style=flat-square

[link_build]:           https://github.com/TheDragonCode/github-notifications/actions

[link_license]:         LICENSE

[link_packagist]:       https://packagist.org/packages/dragon-code/github-notifications
