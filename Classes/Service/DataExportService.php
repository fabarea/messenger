<?php

namespace Fab\Messenger\Service;

use Fab\Messenger\Domain\Repository\SentMessageRepository;
use InvalidArgumentException;
use SimpleXMLElement;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DataExportService
 */
class DataExportService implements SingletonInterface
{
    protected SentMessageRepository $sentMessageRepository;

    public function __construct()
    {
        $this->sentMessageRepository = GeneralUtility::makeInstance(SentMessageRepository::class);
    }

    /**
     * Returns a class instance
     *
     * @return DataExportService
     * @throws InvalidArgumentException
     */
    public static function getInstance()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    public function exportCsv(
        array $uids,
        string $filename,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '\\',
    ): void {
        $dataSets = $this->sentMessageRepository->findByUids($uids);
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, array_keys($dataSets[0]), $delimiter, $enclosure, $escape);
        foreach ($dataSets as $data) {
            fputcsv($csv, $data, $delimiter, $enclosure, $escape);
        }
        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $csvContent;
        exit();
    }

    public function exportXls(array $dataUids, string $filename): void
    {
        // todo fix me!
        foreach ($dataUids as $uid) {
            $data[] = $this->sentMessageRepository->findByUid($uid);
        }
        $xls = fopen('php://temp', 'r+');
        fputcsv($xls, array_keys($data[0]), "\t");
        foreach ($data as $row) {
            fputcsv($xls, $row, "\t");
        }
        rewind($xls);
        $xlsContent = stream_get_contents($xls);
        fclose($xls);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $xlsContent;
        exit();
    }

    public function exportXml(array $dataUids, string $filename): void
    {
        foreach ($dataUids as $uid) {
            $data[] = $this->sentMessageRepository->findByUid($uid);
        }
        $xml = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
        foreach ($data as $row) {
            $item = $xml->addChild('item');
            foreach ($row as $key => $value) {
                $item->addChild($key, $value);
            }
        }
        $xmlContent = $xml->asXML();

        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $xmlContent;
        exit();
    }
}
