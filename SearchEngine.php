<?php

/**
 * SearchEngine Class
 *
 * Author: Rendfall
 * CreatedAt: 24.09.2015
 * Version: 1.0
 * Source: https://github.com/rendfall/WP-SearchEngine
 *
 * @example
 * $SE = new SearchEngine();
 * $results = $SE->fetchResults();
 */

class SearchEngine {
    // Parametry wyszukania (`$_GET`).
    private $queryString = '';
    // Argumenty, wedle których mamy szukać.
    private $args;
    // Wyniki wyszukania - odpowiednik `$WP_Query->posts`.
    private $posts;
    // WordPress-owy obiekt zapytania.
    private $WP_Query;
    // Symbol, który służy jako flaga wyszukiwania bez frazy.
    const NO_PHRASE_SYMBOL = '1';

    /**
     * Konstruktor - pobieramy tutaj parametry z tablicy `$_GET` oraz ustawiamy domyślne argumenty.
     *
     * @return {this}
     */
    public function __construct() {
        $this->queryString = isset($_GET) ? $_GET : array();
        $this->_setArgs();

        return $this;
    }

    /**
     * Modyfikacja argumentów dla `WP_Query`.
     *
     * @param {Array} $args Tablica argumentów.
     */
    private function _setArgs($args = array()) {
        $defaults = array(
            'posts_per_page' => -1,
            'post_type' => 'page',
            'post_status' => 'publish',
            'suppress_filters' => false,
            'order' => 'ASC',
            'orderby' => 'menu_order'
        );

        // Sprawdź czy podano frazę.
        $s = $this->queryString['s'];
        if ($s !== self::NO_PHRASE_SYMBOL) {
            $defaults['s'] = $s;
        }

        // Pobierz filtry.
        $filters = $this->getSearchFilters();
        // Ustal filtry tylko jeśli podane.
        if ($filters) {
            $defaults['meta_query'] = $filters;
        }

        // Merguj z domyślnymi i zapisz do `args`.
        $this->args = wp_parse_args($args, $defaults);
    }

    /**
     * Start wyszukiwania.
     *
     * @return {Array}
     */
    private function _fetchResults() {
        // Utwórz zapytanie.
        $this->WP_Query = new WP_Query($this->args);
        // Zapisz wyniki.
        $this->posts = $this->WP_Query->posts;

        return $this->posts;
    }

    /**
     * Pobieranie filtrów
     *
     * @private
     * @return {Array}
     */
    private function getSearchFilters() {
        $filters = array();
        $queryString = $this->queryString;
        // Usuń frazę wyszukania, bo to nie filtr.
        unset($queryString[s]);
        
        foreach ($queryString as $key => $value) {
            // Jeśli argument nie pusty...
            if ($value) {
                $filters[] = array(
                    'key' => $key,
                    'value' => $value,
                    'compare' => '='
                );
            }
        }

        // Jeśli zebrano jakieś filtry - dodaj relację.
        if ($filters) {
            $filters['relation'] = 'AND';
        }

        return $filters;
    }

    /** API: Ustawianie argumentów */
    public function setArgs($args = array()) { $this->_setArgs($args); }
    /** API: Wygeneruj zapytanie. */
    public function fetchResults() { return $this->_fetchResults(); }
}
