<?php
/**
 * Language support functions for Admin Panel
 */

/**
 * Get the current language from session or set to default
 * 
 * @return string Language code (en, tr, etc.)
 */
function getCurrentLanguage() {
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }
    
    // Default language is English
    return 'en';
}

/**
 * Set the current language in session
 * 
 * @param string $language Language code (en, tr, etc.)
 * @return bool Success status
 */
function setLanguage($language) {
    // Validate language
    $availableLanguages = getAvailableLanguages();
    if (!in_array($language, array_keys($availableLanguages))) {
        return false;
    }
    
    $_SESSION['language'] = $language;
    return true;
}

/**
 * Get available languages
 * 
 * @return array Array of available languages with code => name
 */
function getAvailableLanguages() {
    return [
        'en' => 'English',
        'tr' => 'Türkçe'
    ];
}

/**
 * Load language file
 * 
 * @param string $language Language code (en, tr, etc.)
 * @return array Language strings
 */
function loadLanguage($language = null) {
    if ($language === null) {
        $language = getCurrentLanguage();
    }
    
    $languageFile = __DIR__ . '/languages/' . $language . '.php';
    
    if (file_exists($languageFile)) {
        return require $languageFile;
    }
    
    // Fallback to English if language file doesn't exist
    return require __DIR__ . '/languages/en.php';
}

/**
 * Translate a string
 * 
 * @param string $key Translation key
 * @param array $params Parameters to replace in the string
 * @return string Translated string
 */
function __($key, $params = []) {
    static $translations = null;
    
    if ($translations === null) {
        $translations = loadLanguage();
    }
    
    $translation = isset($translations[$key]) ? $translations[$key] : $key;
    
    // Replace parameters
    if (!empty($params)) {
        foreach ($params as $param => $value) {
            $translation = str_replace(':' . $param, $value, $translation);
        }
    }
    
    return $translation;
}
