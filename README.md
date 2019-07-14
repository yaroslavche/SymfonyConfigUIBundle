[![Build Status](https://travis-ci.org/yaroslavche/SymfonyConfigUIBundle.svg?branch=master)](https://travis-ci.org/yaroslavche/SymfonyConfigUIBundle)
```bash
$ composer require yaroslavche/config-ui-bundle
```
```yaml
# config/routes/yaroslavche_config_ui.yaml
yaroslavche_config_ui:
    resource: "@YaroslavcheConfigUIBundle/Resources/config/routes.xml"
    prefix: '/config'
```

`https://localhost:8000/config/dashboard`
