# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [0.2-alpha2] - 2022-02-15
### Removed
* Removed the `Importer` argument from the `Engine` class constructor due to circular dependency.

## [0.2-alpha] - 2022-02-15
### Added
* New `Importer` class with corresponding `ImportStrategy` and `ImportScheduler` interfaces.
* New `ImportedBatch` class which is used as a result DTO by the importer.
* New `Marker` interface to abstract persistent flags and a `NullMarker` noop implementation.
* Two new methods in the `ConversionStrategy` class: `beforeBatch()` and `afterBatch()`.
* `ConversionStrategy` implementations can throw a `ConversionShortCircuit` exception to stop conversion early.

### Changed
* The `Engine` class constructor now requires an `Importer` argument.

## [0.1-alpha.2] - 2021-07-15
### Added
* `FetchQuery` instances created from a result now accrue the total number of fetched items.

## [0.1-alpha] - 2021-07-15
Initial version
