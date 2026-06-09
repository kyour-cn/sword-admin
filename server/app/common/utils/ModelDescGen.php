<?php

namespace app\common\utils;

use support\Db;

/**
 * 
 */
class ModelDescGen
{

    /**
     * 自动更新模型类注释
     * 扫描app/model目录下的模型类，自动生成字段和关联关系注释
     */
    public function genAllModelDesc(string $modelPath = 'app/model'): void
    {
        // 使用绝对路径，避免依赖base_path函数
        $modelDir = dirname(__DIR__, 3) . "/$modelPath";

        if (!is_dir($modelDir)) {
            echo "模型目录不存在: $modelDir\n";
            return;
        }

        $files = scandir($modelDir);

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            $filePath = $modelDir . '/' . $file;
            $this->updateModelFileComment($filePath);
        }

        echo "模型注释更新完成！\n";
    }
    
    /**
     * 更新单个模型文件的注释
     * @param string $file 模型文件路径
     * @return bool 是否成功更新
     */
    public function genModeDesc(string $file): bool
    {
        if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
            echo "文件不是PHP文件: $file\n";
            return false;
        }
        
        if (!file_exists($file)) {
            echo "文件不存在: $file\n";
            return false;
        }
        
        // 使用现有的updateModelFileComment方法
        $this->updateModelFileComment($file);
        return true;
    }

    /**
     * 获取数据表字段生成模型ide注解
     * @param string $table
     * @return string
     * @api /index/test/fields?name=role
     */
    public function genDesc(string $table): string
    {
        $fields = Db::getSchemaBuilder()->getColumns($table);

        //查询表备注
        try{
            $tableComment = Db::select("SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE table_name = '$table'");
            if ($tableComment && !empty($tableComment[0]->TABLE_COMMENT)){
                $tableComment = " * {$tableComment[0]->TABLE_COMMENT}\n";
            } else {
                $tableComment = '';
            }
        }catch (\Exception $e){
            $tableComment = '';
        }

        $res = "/**\n$tableComment";

        foreach ($fields as $v){
            $type = $v['type'];
            $comment = $v['comment'] ?? '';
            $type = str_replace(' unsigned', '', $type);

            //数据类型转换
            $type = match ($type) {
                "smallint", "int", "bigint", "tinyint" => 'int',
                "decimal" => 'double',
                default => 'string',
            };

            $res .= " * @property $type \${$v['name']} $comment\n";
        }
        $res .= " */";

        return $res;
    }

    /**
     * 更新单个模型文件的注释
     */
    private function updateModelFileComment(string $filePath): void
    {
        $content = file_get_contents($filePath);
        
        // 获取类名和命名空间
        $namespace = $this->extractNamespace($content);
        $className = $this->extractClassName($content);
        
        if (!$namespace || !$className) {
            echo "无法解析文件: $filePath\n";
            return;
        }
        
        // 获取表名
        $tableName = $this->extractTableName($content);
        if (!$tableName) {
            echo "无法获取表名，跳过: $filePath\n";
            return;
        }
        
        // 生成字段注释
        $fieldComments = $this->genDesc($tableName);
        
        // 解析关联关系
        $relationComments = $this->extractRelationComments($content, $namespace);
        
        // 合并注释
        $newComment = $this->mergeComments($fieldComments, $relationComments);
        
        // 更新文件注释
        $updatedContent = $this->replaceClassComment($content, $newComment);
        
        if ($updatedContent !== $content) {
            file_put_contents($filePath, $updatedContent);
            echo "已更新: $filePath\n";
        } else {
            echo "无需更新: $filePath\n";
        }
    }
    
    /**
     * 提取命名空间
     */
    private function extractNamespace(string $content): ?string
    {
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
    
    /**
     * 提取类名
     */
    private function extractClassName(string $content): ?string
    {
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * 提取表名
     */
    private function extractTableName(string $content): ?string
    {
        if (preg_match("/protected\\s+\\\$table\\s*=\\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * 提取关联关系注释
     */
    private function extractRelationComments(string $content, string $namespace): string
    {
        $relationComments = '';
        
        // 匹配关联方法
        if (preg_match_all('/public\s+function\s+(\w+)\s*\(\s*\)\s*:\s*(\w+)/', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $methodName = $match[1];
                $returnType = $match[2];
                
                // 获取方法体内容
                $methodBody = $this->extractMethodBody($content, $methodName);
                
                if ($methodBody) {
                    $relationComment = $this->parseRelationMethod($methodBody, $methodName, $returnType, $namespace);
                    if ($relationComment) {
                        $relationComments .= $relationComment;
                    }
                }
            }
        }
        
        return $relationComments;
    }
    
    /**
     * 提取方法体内容
     */
    private function extractMethodBody(string $content, string $methodName): ?string
    {
        $pattern = '/public\s+function\s+' . $methodName . '\s*\([^)]*\)\s*:[^\n]*\n\s*\{([^}]*)}/';
        
        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * 解析关联方法
     */
    private function parseRelationMethod(string $methodBody, string $methodName, string $returnType, string $namespace): ?string
    {
        // 检查是否是关联方法
        if (!str_contains($methodBody, 'hasMany') && !str_contains($methodBody, 'hasOne') && 
            !str_contains($methodBody, 'belongsTo') && !str_contains($methodBody, 'belongsToMany')) {
            return null;
        }
        
        // 解析关联类型和关联模型
        $relationType = '';
        $relatedModel = '';
        
        if (preg_match('/->(hasMany|hasOne|belongsTo|belongsToMany)\(\s*([^,]+)::class/', $methodBody, $matches)) {
            $relationType = $matches[1];
            $relatedModel = trim($matches[2]);
            
            // 如果是相对命名空间，添加完整命名空间
            if (!str_contains($relatedModel, '\\')) {
                $relatedModel = $namespace . '\\' . $relatedModel;
            }
            
            // 根据关联类型确定属性类型
            $propertyType = $this->getRelationPropertyType($relationType, $relatedModel);
            $relationDesc = $this->getRelationDescription($relationType);
            
            // 优化：如果关联模型在当前命名空间，则省略命名空间前缀
            $displayModel = $relatedModel;
            $displayPropertyType = $propertyType;
            if (str_starts_with($relatedModel, $namespace . '\\')) {
                $displayModel = substr($relatedModel, strlen($namespace) + 1);
                $displayPropertyType = substr($propertyType, strlen($namespace) + 1);
            }
            
            return " * @property $displayPropertyType \$$methodName {$displayModel}模型$relationDesc\n";
        }
        
        return null;
    }
    
    /**
     * 获取关联属性类型
     */
    private function getRelationPropertyType(string $relationType, string $relatedModel): string
    {
        return match ($relationType) {
            'hasMany', 'belongsToMany' => $relatedModel . '[]',
            default => $relatedModel,
        };
    }
    
    /**
     * 获取关联关系描述
     */
    private function getRelationDescription(string $relationType): string
    {
        $descriptions = [
            'hasMany' => '一对多关联',
            'hasOne' => '一对一关联', 
            'belongsTo' => '从属关联',
            'belongsToMany' => '多对多关联'
        ];
        
        return $descriptions[$relationType] ?? '关联';
    }
    
    /**
     * 合并字段注释和关联关系注释
     */
    private function mergeComments(string $fieldComments, string $relationComments): string
    {
        if (empty($relationComments)) {
            return $fieldComments;
        }
        
        // 移除字段注释的结束标记
        $fieldComments = rtrim($fieldComments, " */");
        
        // 添加关联关系注释
        $fieldComments .= $relationComments;
        
        // 重新添加结束标记
        $fieldComments .= " */";
        
        return $fieldComments;
    }
    
    /**
     * 替换类注释
     */
    private function replaceClassComment(string $content, string $newComment): string
    {
        // 匹配现有的类注释（如果有）
        $pattern = '/\/\*\*[^*]*\*+(?:[^*\/][^*]*\*+)*\/\s*class/'; 
        
        if (preg_match($pattern, $content)) {
            // 替换现有注释
            return preg_replace($pattern, $newComment . "\nclass", $content, 1);
        } else {
            // 在类定义前添加新注释
            return preg_replace('/(class\s+\w+)/', $newComment . "\n$1", $content, 1);
        }
    }
}