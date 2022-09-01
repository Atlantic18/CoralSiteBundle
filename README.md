README
======

What is Coral?
-----------------

[Coral][1] is a different approach towards content management. It's based upon a believe that the future will be Microservice architecture containing the business logic. The place for Coral in this architecture is aggregation of the Microservices and providing interaction with the user. The main problem of complex CMS is a steep learning curve for a developer in a need to do changes other than simple content rendering.

The basic principles embedded is creation of a very light and simple layer. Instead of inventing and maintaining new technologies already existing ones were used. You won't find any UI or storage limitations. Feel free to use whatever fits your needs: Github for content branching or Dropbox for quick collaboration. You need to manage your site on mobile? Use your favorite editor for both content and the code.

CoralSiteBundle
----------------

CoralSiteBundle is one of the Coral bundles and contains logic for content rendering and route handling.

Documentation
----------------------

You can find detailed documentation on the [official website][1].

Tests
----------------------

Test suites are part of the bundle and code is fully covered. You can find details at [travis-ci.org][2]

Docker
----------------------

```
docker build -t bundle_symfony:latest .
docker run -v `pwd`:/app bundle_symfony composer update
docker run -v `pwd`:/app bundle_symfony php vendor/bin/phpunit
```

![Travis-ci.org](https://travis-ci.org/Atlantic18/CoralSiteBundle.svg?branch=master)

[1]: https://coral.atlantic18.com
[2]: https://travis-ci.org/Atlantic18/CoralSiteBundle