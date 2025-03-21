# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Added support for defer scripts
- Support in the `DataValidator` Twig extension for field names using a variable

### Changed

- The `RequestResult::buildRedirectRequestResult` method now accepts a second parameter to easily add query parameters to the URL
- Renamed the `DoNotExistInDB` class in `DoesNotExistInDB`

### Improved

- The `mfx_users.user_created` SQL table field has now a `CURRENT_TIMESTAMP` default value to simplify user creation
- Modernized the `DataValidator` Twig extension

### Fixed

- Incompatible type errors in the `PaginationManager::getCurrentPageIndex()` and `PaginationManager::getPageCount()` methods
- Database connections are not properly released by the close function
- Avoid returning sensitive information in the User instance
- Fixed a legacy bug in the `DataValidator` Twig extension
- Fixed an issue with extra parameters and some `DataValidator`'s field types
- Fixed a compilation error within the `ExistsInDB` and `DoesNotExistInDB` filter classes
- Fixed an issue preventing `DataValidator`'s optional `WithOptions` fields to validate their value properly
- Script and stylesheets URLs were incorrectly espaced
- Fixed PHP 8.4 deprecation warnings
- Fixed an issue with the `DataValidator` integer field types

## [2.0.1] - 2024-11-23

### Added

- `getRequestContentType` function to the `IRequestService` interface to retrieve the content type used by the request

### Improved

- Made `RequiredRequestMethod` and `RequiredContentType` attributes repeatable

### Fixed

- Extraneous invalid parameter when throwing an exception
- Wrong variable used within the `RequiredRequestMethod` constructor

## [2.0.0] - 2024-10-02

### Added

- Add tests for some of the services
- Support for additional configuration options for scripts and stylesheets

### Changed

- Refactored most static classes as services
- Modernized session management
- Renamed the built-in `Status` route to `AppStatus` for clarity

### Improved

- Enabled strict types
- The unhandled exception handler now outputs full exception stack in accordance with the `response.full_errors` configuration directive

### Fixed

- Fixed some bugs within the database updater

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
