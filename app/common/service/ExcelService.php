<?php

namespace app\common\service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use support\Response;

/**
 * 封装文件导入导出服务
 * require: composer require phpoffice/phpspreadsheet:^1.28.0
 * Class ExcelService
 */
class ExcelService
{

    private array $colsIndex = [];
    private int $lineIndex = 1; //当前写入行光标

    private Spreadsheet $spreadsheet;
    private Xlsx $excel;
    private Worksheet $sheet;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();

        // 实例化excel
        $this->excel = new Xlsx($this->spreadsheet);

    }

    /**
     * get当前写入行光标
     * @return int
     */
    public function getLineIndex(): int
    {
        return $this->lineIndex;
    }

    /**
     * set当前写入行光标
     * @param int $lineIndex
     * @return self
     */
    public function setLineIndex(int $lineIndex): self
    {
        $this->lineIndex = $lineIndex;
        return $this;
    }

    /**
     * @param array $cols
     * @return self
     */
    public function setCols(array $cols): self
    {
        $this->colsIndex = $this->makeColumns(count($cols));

        // 对单元格设置居中效果
        $index = 0;
        foreach ($cols as $colName => $colWidth) {
            $key = $this->colsIndex[$index++];

            $this->sheet->setCellValue($key . '1', $colName);
            $this->sheet->getColumnDimension($key)->setWidth($colWidth);
        }
        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->sheet->setTitle($title);
        return $this;
    }

    public function writeLine(array $data, int $line = 0): self
    {
        if($line == 0){
            $line = $this->lineIndex +1;
        }
        foreach ($data as $key => $val){
            $colKey = $this->colsIndex[$key];
            $this->sheet->setCellValue($colKey . $line, $val);
        }
        $this->lineIndex = $line;
        return $this;
    }

    /**
     * 保存数据到文件
     * @param string $fileName
     * @param string $path
     * @return string[]
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function saveToFile(string $fileName, string $path = ''): array
    {
        $fileName = "{$fileName}.xlsx";
        if(!$path){
            $path = "/download/".date('Ymd');
        }

        $public = public_path();

        //创建文件夹
        if(!is_dir($public. $path)) mkdir($public. $path, 0777, true);

        $rand = rand(0,999999);
        $file = $path. "/{$rand}_". $fileName;

        // 保存文件到 public 下
        $this->excel->save($public. $file);

        return [
            'path' => $path,
            'fileName' => $fileName,
            'file' => $file
        ];
    }

    /**
     * 直接下载表格文件
     * @param string $fileName
     * @param string $path
     * @return Response
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function downloadFile(string $fileName, string $path = ''): Response
    {
        //TODO: 应改为文件流的形似，避免存到硬盘再去读取下载
        $save = $this->saveToFile($fileName, $path);

        // 下载文件
        return response()->download($save['file'], $save['fileName']);
    }

    /**
     * @param int $index max:256
     * @return string
     */
    private function getColumn(int $index): string
    {
        $a1 = floor($index / 26); //第一位的ASCII码序号
        $yu = $index % 26;
        $s = '';
        if ($yu == 0) {
            $yu = 26;
            $a1 = $a1 - 1;
        }
        if ($a1 > 0) {
            $s = chr(64 + $a1);
        }
        $s .= chr(64 + $yu);
        return $s;
    }

    /**
     * @param int $max
     * @return array
     */
    private function makeColumns(int $max): array
    {
        $cols = [];
        for($i = 1; $i <= $max; $i++){
            $cols[] = $this->getColumn($i);
        }
        return $cols;
    }

}