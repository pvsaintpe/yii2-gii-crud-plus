# yii2-gii-plus

Yii 2 Gii Plus Extension

## Base Model Generator
```
yii gii/base_model --interactive=0
```
This command generates base models like the following:
* models/base/ModelBase.php
* models/query/base/ModelQueryBase.php

## Custom Model Generator
```
yii gii/custom_model --interactive=0
```
This command generates base models like the following:
* models/Model.php
* models/query/ModelQuery.php

## After Custom Models!
You should regenerate base models after custom models twice.
```
yii gii/base_model --interactive=0 --overwrite=1
yii gii/base_model --interactive=0 --overwrite=1
```
A first regeneration inserts relations.
A second regeneration updates phpDoc @return directives.


    "yiisoft/yii2": ">=2.0.4",
    "yiisoft/yii2-gii": "2.0.5",
    "yiisoft/yii2-boost": "*",
    "yiisoft/yii2-jui-autocomplete": "*"