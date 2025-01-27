<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['category'])) {
    $categories_slug = strval($_POST['category']);
    foreach ($_POST as $items){
        if (is_array($items)){
            $output = [];
            $index = 1;
            foreach ($items as $item){
                $label = strval($item['label']);
                $type = strval($item['type']);
                $isRequired = (bool)$item['required'];
                $options = $item['options'];
                //حذف فاصله های اضافی از ابتدا و انتهای رشته
                $options = trim($options);
                //تبدیل به آرایه
                $parts = explode('-', $options);
                //حذف فضاهای اضافی اطراف هر عنصر
                $cleanParts = array_map('trim', $parts);
//حذف عناصر خالی (در صورت وجود)
                $cleanOptions = array_filter($cleanParts, function ($a) {
                    return !empty($a);
                });

                if ($type == 'range') {
                    $finalData = [
                        "label" => $label,
                        "sort" => $index++,
                        "name" => uniqid().rand(111,999),//جهت وجود یک بخش منحصر به فرد
                        "type" => $type,
                        "is_required" => $isRequired,
                        "categories_slug" => $categories_slug,
                        "min_range" => $cleanOptions[0] ?? null,
                        "max_range" => $cleanOptions[1] ?? null,
                ];
                }else{
                    $simpleOptions = array_values($cleanOptions);
                    $finalData = [
                        "label" => $label,
                        "sort" => $index++,
                        "name" => uniqid().rand(111,999),//جهت وجود یک بخش منحصر به فرد
                        "type" => $type,
                        "is_required" => $isRequired,
                        "categories_slug" => $categories_slug,
                        "options" => $simpleOptions,
                    ];
                }


                $output[] = $finalData;

                /*echo $categories_slug.'<hr>';
                echo $label.'<br>'.$type.'<br>'.$required.'<br>';
                echo '<pre>';
                print_r($cleanParts);
                echo '</pre>';*/

            }
            $jsonData = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            if ($jsonData === false) {
                echo "خطایی در تبدیل داده‌ها به JSON رخ داده است.";
                exit;
            }

            //ایجاد پوشه filters برای ذخیره فایل JSON
            if (!is_dir( dirname(__DIR__) . '/filters')) {
                mkdir( dirname(__DIR__) . '/filters', 0777, true);
            }

            $fileName =  dirname(__DIR__) . '/filters/'.$categories_slug.'.json';
            if (file_put_contents($fileName, $jsonData)) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'فایل با موفقیت ساخته شد',
                    'data' => $output
                ]);
            } else {
                header('Content-Type: application/json');
                http_response_code(405); // Method Not Allowed
                echo json_encode([
                    'status' => 'error',
                    'message' => 'خطایی در ایجاد فایل JSON رخ داد.'
                ]);
            }
        }
    }
} else {
    header('Content-Type: application/json');
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'status' => 'error',
        'message' => 'دسته بندی ارسال نشده است.'
    ]);
}