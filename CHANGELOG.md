# Site Sync Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.2.0 - 2020-04-23

### Changed

- "Overwrite" now works with translatable matrix fields. The behavior is leveraged from `\craft\base\Element::propagateAll`, mimicking the behavior when you create a new entry.

## 1.1.1 - 2020-04-22

### Fixed

- Insuccifient `fields` check (thanks @qbasic16…now I see why you did that 😉)

## 1.1.0 - 2020-03-25

### Changed

- Field sync updated for Craft ^3.2 (thanks @qbasic16)

### Fixed

- ID comparison between string and int now correctly compares only in int type (thanks @qbasic16)

## 1.0.3 - 2019-04-09

### Fixed

- Fixed Matrix/Neo/SuperTable syncing after 1.0.0-beta.1 checked for permissions (thanks @jesuismaxime)

## 1.0.2 - 2019-02-05

### Fixed

- Fix alias after name change

## 1.0.1 - 2019-01-09

### Fixed

- Enforcing 7.1 requirement

## 1.0.0 - 2019-01-04

### Added

- Initial release
- Docs
- Change namespace

## 1.0.0-beta.1 - 2018-12-06

### Added

- Only sync to sites that user has publish permissions for
