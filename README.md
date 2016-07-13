**MEMOBIRD PHP LIBRARY**

A PHP Memobird library

咕咕机PHP库


**Feature 只要功能**

1.Add text 增加文字

2.Add text image width options 增加文字的图片 (默认左对齐(1.0.4), 可设定左对齐，居中， 右对齐)

3.Add Line 增加分隔线

4.Add QR Code 增加二维码

5.Add Photo 增加图片

6.Add printed time 增加列印时间 (v.1.0.3 13/7/2016)


**Installation 安装**

```
$ composer require atans/memobird
```

**DEMO 演示**

![Memobird Demo](https://raw.githubusercontent.com/atans/memobird/master/demo.jpg)


**Example 例子**

```php
<?php
require 'vendor/autolod.php';

use Atans\Content\PrintContent;
use Atans\Memobird\Memobird;

$memobird = new Memobird('API KEY');

$memobirdId = 'Your memobird device id';

$printContent = new PrintContent();

// Set font
// 设置字体
$printContent->setFont('path/to/font.ttf');

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
// or
$printContent->addTextImage('Hello world', [
    'align' => PrintContent::ALIGN_CENTER,
    'font' => 'path/to/font.ttf',
     // ... more option please see src/Memobird/Content/PrintContent.php
     // ... 更多设置请看 src/Memobird/Content/PrintContent.php
]);


// Add a line
// 加一条线
$printContent->addLine();

// Add a Qr Code
// 增加 二维码
$printContent->addQrCode('http://memobird.cn');
$printContent->addQrCode('http://memobird.cn', [
    'logo' => 'path/to/logo.jpg',
    // ... more option please see src/Memobird/Content/PrintContent.php
    // ... 更多设置请看 src/Memobird/Content/PrintContent.php
]);

// Add printed time
// 加列印时间
$printContent->addPrint('http://memobird.cn');

    
// Remove all content
// 刪除所有內容
// $printContent->removeAll();

// Print
// 打印
$printPaperResult = $memobird->printPaper($memobirdId, $printContent);

// Get print status
// 取得打印状态
$printStatusResult = $memobird->printStatus($printPaperResult->getPrintcontentid());
    
```



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
