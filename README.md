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

And config*:
```yaml
# config/packages/yaroslavche_config_ui.yaml
yaroslavche_config_ui:
  definition_fields:
    name: true
    normalization: false
    validation: false
    defaultValue: true
    default: true
    required: true
    deprecationMessage: true
    merge: false
    allowEmptyValue: true
    nullEquivalent: false
    trueEquivalent: false
    falseEquivalent: false
    pathSeparator: false
    parent: false
    attributes: true
    performDeepMerging: false
    ignoreExtraKeys: false
    removeExtraKeys: false
    children: true
    prototype: true
    atLeastOne: true
    allowNewKeys: false
    key: false
    removeKeyItem: false
    addDefaults: false
    addDefaultChildren: false
    nodeBuilder: false
    normalizeKeys: false
    min: false
    max: false
    values: false
    type: true
```
`*` I suppose it will be default in future. `definition_fields` - private properties of `NodeDefinition` (depends on NodeDefinition class). Boolean: include in definitions array or not.


Look at:

[https://localhost:8000/config/bundles](https://localhost:8000/config/bundles)
```js
const response = {
  "status": "success",
  "bundles": {
    // ...
  }
}
```
[https://localhost:8000/config/bundle/FrameworkBundle](https://localhost:8000/config/bundle/FrameworkBundle)
```js
const response = {
  "status": "success",
  "bundle": {
    "name": "FrameworkBundle",
    "namespace": "Symfony\\Bundle\\FrameworkBundle",
    "path": "\/vendor\/symfony\/framework-bundle",
    "definitions": {
      "secret": {
        "name": "secret",
        "defaultValue": null,
        "default": false,
        "required": false,
        "deprecationMessage": null,
        "allowEmptyValue": true,
        "attributes": [],
        "type": "scalar"
      },
      // ...
    },
    // ...
  }
}
```

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
