<?php

namespace Fab\Messenger\Service;

use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
use Fab\Messenger\Domain\Repository\MessageTemplateRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use InvalidArgumentException;
use JetBrains\PhpStorm\NoReturn;
use SimpleXMLElement;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DataExportService
 */
class DataExportService implements SingletonInterface
{
    protected SentMessageRepository|MessageLayoutRepository|MessageTemplateRepository $repository;

    /**
     * Returns a class instance
     *
     * @return DataExportService
     * @throws InvalidArgumentException
     */
    public static function getInstance(): DataExportService
    {
        return GeneralUtility::makeInstance(self::class);
    }

    public function setRepository(
        SentMessageRepository|MessageLayoutRepository|MessageTemplateRepository $repository,
    ): void {
        $this->repository = $repository;
    }

    public function exportCsv(
        array $uids,
        string $filename,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '\\',
        array $header = [],
    ): void {
        $dataSets = $this->repository->findByUids($uids);
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, $header, $delimiter, $enclosure, $escape);
        foreach ($dataSets as $dataSet) {
            $row = [];
            foreach ($header as $key) {
                $row[] = $dataSet[$key];
            }
            fputcsv($csv, $row, $delimiter, $enclosure, $escape);
        }
        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $csvContent;
        exit();
    }

    #[NoReturn]
    public function exportXls(array $dataUids, string $filename, array $header): void
    {
        $dataSets = $this->repository->findByUids($dataUids);
        $xls = fopen('php://temp', 'r+');
        fputcsv($xls, $header, "\t");
        foreach ($dataSets as $dataSet) {
            $computedRow = [];
            foreach ($header as $key) {
                $computedRow[] = $dataSet[$key];
            }
            fputcsv($xls, $computedRow, "\t");
        }
        rewind($xls);
        $xlsContent = stream_get_contents($xls);
        fclose($xls);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $xlsContent;
        exit();
    }

    #[NoReturn]
    public function exportXml(array $dataUids, string $filename, array $header): void
    {
        $dataSets = $this->repository->findByUids($dataUids);
        $xml = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
        $xml->addChild('header', implode(',', $header));
        foreach ($dataSets as $dataSet) {
            $xmlRow = $xml->addChild('row');
            foreach ($dataSet as $key => $value) {
                if (in_array($key, $header, true)) {
                    $xmlRow->addChild($key, $value);
                }
            }
        }
        $xmlContent = $xml->asXML();
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $xmlContent;
        exit();
    }
}
