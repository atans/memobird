**MEMOBIRD PHP LIBRARY**

An Unofficial memobird library
一个非官方咕咕机库

**Installation 安装**

```
$ composer require atans/memobird
```

** DEMO 演示**

![Memobird Demo](https://raw.githubusercontent.com/atans/memobird/master/demo.jpg)


**Example 例子**

```php
<?php
require 'vendor/autolod.php';

$memobird = new \Atans\Memobird('API KEY');

$memobirdId = 'Your memobird device id';

$printContent = new \Atans\Content\PrintContent();

// Add a text
// 增加一段文字
$printContent->addText('Hello World'); 

// Add text twice
// 重复加文字
$printContent->addText('Hello World')
    ->addText('Add another text');
    
// Add an photo
// 增加相片
$printContent->addPhoto('path/to/photo.jpg');

// Add photo twice
// 增加多张相片
$printContent->addPhoto('path/to/photo.jpg')
    ->addPhoto('path/another/photo.jpg');
    
// Add an photo from image resource
// 增加相片资源后的内容
$photoContent = file_get_contents('path/to/photo.png');
$printContent->addPhoto($photoContent);

// Add text and photo
// 增加文字和图片
$printContent->addText('Hello World')
    ->addPhoto('path/to/photo.png'); 

// Add a text image
// 增加文字图
$printContent->addTextImage('Hello world');
or
$printContent->addTextImage('Hello world', [
    'align' => self::ALIGN_CENTER,
    'font' => 'path/to/font.ttf',
     // ... more option please see src/Memobird/Content/PrintContent.php
     // ... 更多设置请看 src/Memobird/Content/PrintContent.php
]);


// Add a line
$printContent->addLine();

// Add a Qr Code
// 增加 Qr code
$printContent->addQrCode('http://memobird.cn');
$printContent->addQrCode('http://memobird.cn', [
    'logo' => 'path/to/logo.jpg',
    // ... more option please see src/Memobird/Content/PrintContent.php
    // ... 更多设置请看 src/Memobird/Content/PrintContent.php
]);
    
    
// Remove all content
// 刪除所有內容
$printContent->removeAll();

// Print
// 打印
$printPaperResult = $memobird->printPaper($memobirdId, $printContent);

// print status
// 打印状态
$printStatusResult = $memobird->printStatus($printPaperResult->getPrintcontentid());
    
```


***

**Requirements 要求**

php 5.5
GD

**Official Documentation 官方文档**

http://open.memobird.cn/ (Chinese)

**Thanks 感谢**

GDIndexedColorConverter https://github.com/ccpalettes/gd-indexed-color-converter

Guzzle http://docs.guzzlephp.org

Imagine http://imagine.readthedocs.org/

Monolog https://github.com/Seldaek/monolog

Symfony Serializer http://symfony.com/doc/current/components/serializer.html

QrCode https://github.com/endroid/QrCode

Doctrine Cache http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/reference/caching.html

symfony/property-access http://symfony.com/doc/current/components/property_access/index.html
