<?php
function createFilter()
{
    $categories_slug = "test";
    $data = "
▪️نوع كالا
➕چند انتخابي
⭐️الزامی
- اصل و اورجينال
-غير اصل
-هر دو مورد

▪️نوع ساخت
➕انتخاب
⭐️اختیاری
- جديد
- قديمی

▪️امكانات تصويري
➕چك
⭐️الزامی
- ويدئو دار
- عكس دار
";

    $types = [
        'انتخاب' => 'select',
        'چک' => 'checkbox',
        'چك' => 'checkbox',
        'رنج' => 'range',
        'چند انتخابي' => 'multi-select',
        'چند انتخابی' => 'multi-select',
    ];


    // Regex برای استخراج داده‌ها
    $regex = '/▪️([^\n]+)\n➕([^\n]+)\n(⭐️(?:الزامی|اختیاری))?\n((?:[^\n]+\n)*)/s';
    preg_match_all($regex, $data, $fieldMatches, PREG_SET_ORDER);

    $output = collect();
    $index = 1;

    // پردازش داده‌ها
    foreach ($fieldMatches as $fieldMatch) {
        $label = trim($fieldMatch[1]);
        $type = trim($fieldMatch[2]);

        // بررسی اینکه فیلد الزامی است یا خیر
        $requirement = trim($fieldMatch[3] ?? '');
        $isRequired = $requirement === '⭐️الزامی';

        // استخراج گزینه‌ها
        $options = array_map('trim', explode("\n", trim($fieldMatch[4])));
        $options = array_map(function($option) {
            return ltrim($option, '- ');  // حذف "-" و فضای خالی از ابتدای هر گزینه
        }, $options);

        // ساختار نهایی داده‌ها
        $finalData = [
            "label" => $label,
            "sort" => $index++,
            "name" => time().rand(1111,9999),//جهت وجود یک بخش منحصر به فرد
            "type" => $types[$type] ?? 'unknown',
            "required" => $isRequired,
            "categories_slug" => $categories_slug,
            "options" => $options,
        ];

        $output->push($finalData);
    }



    $jsonData = json_encode($output->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($jsonData === false) {
        echo "خطایی در تبدیل داده‌ها به JSON رخ داده است.";
        exit;
    }

    //در محلی که فایل php قرار دارد یک پوشه با نام filters باید ایجاد شود
    $fileName = __DIR__ . '/filters/'.$categories_slug.'.json';
    if (file_put_contents($fileName, $jsonData)) {
        return  " فایل JSON با موفقیت ایجاد شد:$categories_slug";
    } else {
        return  "خطایی در ایجاد فایل JSON رخ داد.";
    }
}

