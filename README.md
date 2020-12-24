# Doctrine MySQL Spatial Types

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-ghactions]][link-ghactions]

Doctrine support for spatial types and functions for MySQL.

## Install

Via Composer

```bash
$ bcremer/doctrine-mysql-spatial
```

## Supported Versions

```
  matrix:
    php-version:
      - "7.4"
      - "8.0"
    mysql-version:
      - "5.7"
      - "8.0"
```

## Project origins

This project was forked from [creof/doctrine2-spatial](https://github.com/creof/doctrine2-spatial) by Derek J. Lambert.
The origin project seems to be non-active since 2017.

I stripped down this fork to just support recent PHP and MySQL versions. I do not plan to re-introduce support for other Platforms than (Oracle/Percona) MySQL.

- Removed support for PostgreSQL
- Removed support for PHP Versions < 7.4
- Removed support for MySQL Versions < 5.7
- Removed travis-ci build
- Introduced github actions
- Added support for PHP 8.0
- Added support for MySQL 8
- Changed project namespace from `CrEOF\Spatial` to `Bcremer\Spatial`
- Changed composer package name to `bcremer/doctrine-mysql-spatial`

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/bcremer/doctrine-mysql-spatial.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[link-packagist]: https://packagist.org/packages/bcremer/doctrine-mysql-spatial
[ico-ghactions]: https://github.com/bcremer/doctrine-mysql-spatial/workflows/Continuous%20Integration/badge.svg
[link-ghactions]: https://github.com/bcremer/doctrine-mysql-spatial/actions
