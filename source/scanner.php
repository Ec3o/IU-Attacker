<?php
// 引入目标文件
$target_file = '../target/target1.php';
include($target_file);

// 获取文件内容
$file_contents = file_get_contents($target_file);

// 检查全局范围内是否有`unserialize`调用并标记entrypoint
$entrypoint = "";
if (preg_match('/unserialize\((.*?)\)/', $file_contents, $matches)) {
    $entrypoint = "Entrypoint: unserialize(" . trim($matches[1]) . ")\n";
}

// 获取当前加载的所有类
$declared_classes = get_declared_classes();

// 过滤掉PHP内置类，只保留用户定义的类
$declared_classes = array_filter($declared_classes, function($class) {
    $reflection = new ReflectionClass($class);
    return $reflection->isUserDefined();  // 只保留用户定义的类
});

// 定义一个变量来保存分析结果
$analysis_summary = "";
$analysis_summary .= "共有 " . count($declared_classes) . " 个类:" . implode(",", $declared_classes) . "\n";

// 遍历所有类，分析每个类的变量和方法
foreach ($declared_classes as $class) {
    // 使用反射类获取类的详细信息
    $reflectionClass = new ReflectionClass($class);
    $analysis_summary .= "类 " . $reflectionClass->getName() . ":\n";

    // 分析类的变量
    $properties = $reflectionClass->getProperties();
    $analysis_summary .= "    - 变量:\n";
    if (empty($properties)) {
        $analysis_summary .= "        - null\n";
    } else {
        foreach ($properties as $property) {
            $visibility = $property->isPublic() ? 'public' : ($property->isProtected() ? 'protected' : 'private');
            $analysis_summary .= "        - " . $visibility . " " . $property->getName() . "\n";
        }
    }

    // 分析类的方法
    $methods = $reflectionClass->getMethods();
    $analysis_summary .= "    - 方法:\n";
    foreach ($methods as $method) {
        $method_name = $method->getName();
        $analysis_summary .= "        - " . $method_name . "():\n";

        // 获取方法的源代码以检查是否包含危险代码
        $method_source = file_get_contents($method->getFileName());
        $start_line = $method->getStartLine() - 1;
        $end_line = $method->getEndLine();
        $length = $end_line - $start_line;
        $method_code = implode("", array_slice(file($method->getFileName()), $start_line, $length));

        // 检查是否存在eval、system等危险函数
        if (strpos($method_code, 'eval(') !== false || strpos($method_code, 'system(') !== false || strpos($method_code, 'exec(') !== false) {
            $analysis_summary .= "            - " . trim($method_code) . " <-EndPoint RCE\n";
        }

        // 检查是否存在文件读取操作
        if (strpos($method_code, 'file_get_contents(') !== false || strpos($method_code, 'fread(') !== false || strpos($method_code, 'readfile(') !== false) {
            $analysis_summary .= "            - " . trim($method_code) . " <-EndPoint 任意文件读取\n";
        }

        // 检查是否存在文件写入操作
        if (strpos($method_code, 'file_put_contents(') !== false || strpos($method_code, 'fwrite(') !== false || strpos($method_code, 'fputs(') !== false) {
            $analysis_summary .= "            - " . trim($method_code) . " <-EndPoint 任意文件写入\n";
        }
    }
}

// 生成分析结果的文件名
$txt_filename = basename($target_file, ".php") . "_analysis.txt";

// 将结果写入文件
file_put_contents($txt_filename, $entrypoint . $analysis_summary);

echo "分析结果已写入文件: " . $txt_filename . "\n";

?>
