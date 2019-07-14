[![Build Status](https://travis-ci.org/yaroslavche/SymfonyConfigUIBundle.svg?branch=master)](https://travis-ci.org/yaroslavche/SymfonyConfigUIBundle)
[![codecov](https://codecov.io/gh/yaroslavche/SymfonyConfigUIBundle/branch/master/graph/badge.svg)](https://codecov.io/gh/yaroslavche/SymfonyConfigUIBundle)
[![Infection MSI](https://badge.stryker-mutator.io/github.com/yaroslavche/SymfonyConfigUIBundle/master)](https://infection.github.io)

# Install

```bash
$ composer require yaroslavche/config-ui-bundle
```

Add routes:
```yaml
# config/routes/yaroslavche_config_ui.yaml
yaroslavche_config_ui:
    resource: "@YaroslavcheConfigUIBundle/Resources/config/routes.xml"
    prefix: '/config'
```

Look at:
[http://localhost:8000/config/dashboard](http://localhost:8000/config/dashboard)

# Dev tools
```bash
$ composer cscheck
$ composer csfix
$ composer phpstan
$ composer phpunit
$ composer infection
$ composer clover
$ composer bccheck
```
