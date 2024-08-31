<?php

namespace Core;

class ConsoleTable
{
    private $headers;
    private $rows = [];

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function addRow($row)
    {
        $this->rows[] = $row;
    }

    public function getTable()
    {
        $output = '';
        $columnWidths = [];

        // Calculate column widths
        foreach ($this->headers as $header) {
            $columnWidths[$header] = strlen($header);
        }

        foreach ($this->rows as $row) {
            foreach ($row as $column => $value) {
                if (strlen($value) > $columnWidths[$column]) {
                    $columnWidths[$column] = strlen($value);
                }
            }
        }

        // Create header row
        foreach ($this->headers as $header) {
            $output .= str_pad($header, $columnWidths[$header] + 2) . '|';
        }
        $output .= "\n";

        // Create separator row
        foreach ($this->headers as $header) {
            $output .= str_repeat('-', $columnWidths[$header] + 2) . '|';
        }
        $output .= "\n";

        // Create data rows
        foreach ($this->rows as $row) {
            foreach ($this->headers as $header) {
                $output .= str_pad($row[$header], $columnWidths[$header] + 2) . '|';
            }
            $output .= "\n";
        }

        return $output;
    }
}