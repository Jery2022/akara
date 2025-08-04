// frontent/src/components/utils/dateUtils.js

/**
 * Formate une chaîne de date ISO 8601 en 'jj/mm/aaaa'.
 * @param {string} dateString - La chaîne de date à formater (e.g., '2023-10-27').
 * @returns {string} La date formatée, ou une chaîne vide si la saisie est invalide.
 */
export const formatDate = (dateString) => {
    if (!dateString) {
        return '';
    }
    const date = new Date(dateString);
    if (isNaN(date)) {
        return '';
    }
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
};

/**
 * Prend une date en format 'jj/mm/aaaa' et la convertit en format ISO 'aaaa-mm-jj'.
 * C'est utile pour les champs de type 'date' des formulaires HTML.
 * @param {string} dateString - La chaîne de date à convertir (e.g., '27/10/2023').
 * @returns {string} La date formatée en ISO, ou une chaîne vide si la saisie est invalide.
 */
export const toISOString = (dateString) => {
    if (!dateString) {
        return '';
    }
    const [day, month, year] = dateString.split('/');
    if (!day || !month || !year) {
        return '';
    }
    return `${year}-${month}-${day}`;
};
