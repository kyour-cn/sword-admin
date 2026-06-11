<?php

namespace app\task;


use app\admin\services\UserService;
use app\common\utils\ExcelUtils;
use app\model\Task;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;

/**
 * 用户导出任务处理类
 * @api
 */
class ExportUser implements TaskHandlerInterface
{

    public function getName(): string
    {
        return '用户导出';
    }

    public function getDescription(): string
    {
        return '导出用户到表格';
    }

    /**
     *  任务处理
     * @param Task $task
     * @return string
     * @throws IOException|WriterNotOpenedException
     */
    public function run(Task $task): string
    {
        $params = json_decode($task->content, true);

        // 导出文件
        $file = "uploads/export/user_export_$task->id.xlsx";
        $path = public_path() . "/" . $file;

        // 确保导出目录存在
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $eu = new ExcelUtils();
        $eu->openWriter($path);

        $eu->setCols([
            'ID' => 10,
            '昵称' => 20,
            '用户名' => 20,
            '状态' => 10,
            '注册时间' => 20,
            '最后登录时间' => 20
        ]);

        $query = (new UserService())->buildQuery($params ?: []);
        
        $count = 0;

        $query
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($eu, &$count) {
                foreach ($users as $user) {
                    $count++;
                    $eu->writeLine([
                        $user->id,
                        $user->nickname,
                        $user->username,
                        $user->status == 1 ? '正常' : '禁用',
                        $user->created_at->format('Y-m-d H:i:s'),
                        $user->login_time
                    ]);
                }
            });

        $eu->save();

        // 返回结果
        return json_encode([
            'message' => '导出成功',
            'success' => $count,
            'file' => $file
        ], JSON_UNESCAPED_UNICODE);
    }
}
