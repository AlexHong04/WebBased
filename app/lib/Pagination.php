<?php

class Pagination
{
  public $totalRecords;
  public $recordsPerPage;
  public $currentPage;
  public $totalPages;
  public $offset;

  public function __construct($totalRecords, $recordsPerPage = 10, $currentPage = 1)
  {
    $this->totalRecords  = (int) $totalRecords;
    $this->recordsPerPage = (int) $recordsPerPage;
    $this->currentPage   = max(1, (int) $currentPage);

    $this->totalPages = ceil($this->totalRecords / $this->recordsPerPage);

    // Prevent exceeding max pages
    if ($this->currentPage > $this->totalPages) {
      $this->currentPage = $this->totalPages;
    }

    // Calculate offset for SQL LIMIT
    $this->offset = ($this->currentPage - 1) * $this->recordsPerPage;
  }

  public function render(): string
  {
    if ($this->totalPages <= 1) return ''; // no pagination needed

    $html = '<div class="pagination">';

    // Previous button
    if ($this->currentPage > 1) {
      $html .= '<a href="?page=' . ($this->currentPage - 1) . '" class="prev">&laquo;</a>';
    }

    // Page links
    for ($i = 1; $i <= $this->totalPages; $i++) {
      $class = ($i === $this->currentPage) ? 'active' : '';
      $html .= '<a href="?page=' . $i . '" class="' . $class . '">' . $i . '</a>';
    }

    // Next button
    if ($this->currentPage < $this->totalPages) {
      $html .= '<a href="?page=' . ($this->currentPage + 1) . '" class="next">&raquo;</a>';
    }

    $html .= '</div>';

    return $html;
  }
}
