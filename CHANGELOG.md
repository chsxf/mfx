# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Add tests for some of the services

### Changed

- Modernized session management
- Refactored most static classes as services

## [1.0.2] - 2024-07-13

### Fixed

- Incorrect Twig cache folder

## [1.0.1] - 2024-07-10

### Added

- `friendsofphp/php-cs-fixer` and `wikimedia/minify` as dev dependencies

### Improved

- Reorganized files for a cleaner structure
- Syntax consistency

### Changed

- Inlined minified static files (CSS / JS) to avoid having to serve them through the web server
- Updated jQuery to version 3.7.1 slim

## [1.0.0] - 2024-07-09

- Initial release
