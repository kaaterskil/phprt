# PHP Runtime
==============

A PHP implementation of various runtime Java classes and interfaces. Why write Java in PHP you ask? Why introduce type safety over duck typing? Because many of these classes, such as data structures, logging, auto-loading, serialization and reflection either do not exist or are not well implemented in PHP and become useful in frameworks. 

Some of my personal favorites are ServiceLoader, Proxy and ThreadLocal. Data structures such as Queue, Vector, Set and others enforce specific behavior, and HashMap supports objects as keys which native PHP does not. The reflection classes include the ability to read custom annotations embedded in DocComments using the Doctrine Annotation reader. Future work is intended to include a pthreads implementation.

### Requirements and Dependencies

PHP5.5
Doctrine Annotation

### Contribute

Fork and clone to continue what some may call a silly enterprise.
