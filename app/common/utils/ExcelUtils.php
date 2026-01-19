<?php declare(strict_types=1);

namespace app\common\utils;

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Reader\XLSX\Sheet;
use OpenSpout\Reader\XLSX\SheetIterator;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

/**
 * 封装文件导入导出工具
 */
class ExcelUtils
{

    public Writer $writer;

    public Options $options;

    public Reader $reader;

    /**
     * @param string $file
     * @return void
     * @throws IOException
     */
    public function openWriter(string $file): void
    {
        $this->options = new Options();
        $this->writer = new Writer($this->options);
        $this->writer->openToFile($file);
    }

    /**
     * 设置表格列
     * @param array $cols ['列名' => '列宽']
     * @return static
     * @throws IOException
     * @throws WriterNotOpenedException
     */
    public function setCols(array $cols): static
    {
        // 列宽设置
        $colIndex = 1;
        foreach ($cols as $colWidth) {
            $this->options->setColumnWidth($colWidth, $colIndex++);
        }
        $this->writer->addRow(Row::fromValues(array_keys($cols)));

        return $this;
    }

    /**
     * 写入一行数据
     * @param array $data ['值1',['值2', '类型', '居中方式', '字体颜色', '背景颜色', '字体大小']]
     * @return $this
     * @throws IOException|WriterNotOpenedException
     */
    public function writeLine(array $data): static
    {
        $cells = [];
        foreach ($data as $val) {
            $cells[] = Cell::fromValue($val);
        }
        $row = new Row($cells);
        $this->writer->addRow($row);
        return $this;
    }

    /**
     * 保存数据到文件
     * @return void
     */
    public function save(): void
    {
        $this->writer->close();
    }

    /**
     * @param string $file
     * @return void
     * @throws IOException
     */
    public function openReader(string $file): void
    {
        $this->reader = new Reader();
        $this->reader->open($file);
    }

    /**
     * 获取所有工作表
     * @return SheetIterator
     * @throws ReaderNotOpenedException
     */
    public function getSheetIterator(): SheetIterator
    {
        return $this->reader->getSheetIterator();
    }

    /**
     * @return Sheet
     * @throws ReaderNotOpenedException
     */
    public function getCurrentSheet(): Sheet
    {
        return $this->getSheetIterator()->current();
    }

    /**
     * 关闭读取器
     * @return void
     */
    public function close(): void
    {
        $this->reader->close();
    }
}