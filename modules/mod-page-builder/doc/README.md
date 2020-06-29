
# Using the Smart PageBuilder Module for Smart.Framework, rev.20200629

## required settings in etc/config.php
```php
define('SMART_PAGEBUILDER_DB_TYPE', 'sqlite'); // to use PageBuilder with SQLite DB
//define('SMART_PAGEBUILDER_DB_TYPE', 'pgsql'); // or comment the above and uncomment this to use PageBuilder with PostgreSQL DB
```
### for PostgreSQL only, must edit and activate the $configs['pgsql'] from etc/config.php

## optional settings in etc/config.php when using with Pages and Extra Layouts ; Layouts must be in the same folder as the DEFAULT Layout
```php
//define('SMART_PAGEBUILDER_DISABLE_PAGES', true); // this can be set in etc/config.php to disable the use of pages and allow only segments
/* customize and uncomment this to allow set custom templates for pages
$configs['pagebuilder']['layouts'] = [
	'template-3col.htm',
	'template-2col.htm'
];
*/
```

## optional settings in etc/config-admin.php
```php
define('SMART_PAGEBUILDER_DISABLE_DELETE', true); // this can be set in etc/config-admin.php to disable page deletions in PageBuilder Manager (optional)
```

# Managing PageBuilder Pages - Backend:
admin.php?page=page-builder.manage

# Samples - Frontend (requires to install Sample Data in DB from mod-page-builder/models/sql/{postgresql|sqlite}/data/):
index.php?/page/page-builder.test-frontend
index.php?/page/page-builder.test-frontend-segment
index.php?/page/page-builder.test-frontend-segment-with-markers

# Sample YAML Data Definitions

## Sample YAML Data for a Page or Segment ( {{:TEST:}}, {{:AREA-ONE:}} ... {{:AREA-SEVEN:}}, TEMPLATE@* ):
```yaml
RENDER:
  TEST:
    content:
      type: segment
      id: my-segment-2 # html segment
  AREA-ONE:
    content:
      type: segment
      id: my-segment-3 # html segment
  AREA.TWO:
    content-1:
      type: plugin
      id: page-builder/test1
      config:
        title: My Plugin
        columns: 100
#    content-2:
#      type: plugin
#      id: anouncements/main
    content-4:
      type: segment
      id: my-segment-2
    content-3:
      type: segment
      id: my-segment-3 # markdown segment
  AREA-THREE:
    content:
      type: plugin
      id: page-builder/test2
      config: my-segment-5 # settings segment
  AREA-FOUR:
    content:
      type: segment
      id: my-segment-1
  AREA-FIVE:
    content-1:
      type: plugin
      id: page-builder/test3
      config:
        title: News
        columns: 10
    content-2:
      type: segment
      id: my-segment-2
    content-3:
      type: plugin
      id: page-builder/test4
  AREA-SIX:
    content:
      type: value
      id: 'Some <b>Bold Text</b>'
      config:
        syntax: html # valid values here are: 'text' | 'markdown' | 'html' | 'jsval' | 'urlpart' | 'raw' ; 'html' will be trimmed + safe filtered ; 'text' (will be trimmed + escaped as html ; 'markdown' will be trimmed + rendered as html ; jsval will not be trimmed, will be escaped as JS ; 'urlpart' will not be trimmed, will be escaped as RawUrl ; 'raw' will be preserved but requires to be escaped somehow ...
#       escape: js   # valid values here are: 'url' | 'js' | 'num' | 'dec1' | 'dec2' | 'dec3' | 'dec4' | 'int' | 'bool'
  AREA-SEVEN:
    content:
      type: translation
      id: mod-samples.samples.this-is # area.subarea.key
#     config:
#       escape: js
  TEMPLATE@AREA.TOP:
    content:
      type: segment
      id: website-menu
  TEMPLATE@AREA.FOOTER:
    content:
      type: segment
      id: website-footer
  TEMPLATE@TITLE:
    content:
      type: value
      id: This is the page <title>
      translations:
        de: Dies ist die Seite <titel>
        ro: Aceasta este pagina <titlu>
      config:
        syntax: text
```

## Sample YAML Data for Raw Page:
```yaml
PROPS:
  FileName: test.txt
  Disposition: inline
```

## Sample YAML Data for Settings Segment:
```yaml
SETTINGS:
    a: 200
    b: 'this is'
```

## Sample code with extra markers (the extra markers have to be supplied on render page/segment) as [ 'SAMPLE-MARKER1' => 'this is a sample marker that have been post-rendered (will be html escaped in code)', 'SAMPLE-MARKER2' => 'other marker (will be js escaped in code)', ... ]

{{=#SAMPLE-MARKER1|html#=}}
{{=#SAMPLE-MARKER2|js#=}}
...

##### END
