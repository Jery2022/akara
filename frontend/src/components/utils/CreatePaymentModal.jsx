// frontend/src/components/utils/CreatePaymentModal.jsx

import React, { useState, useEffect } from 'react';
import { X } from 'lucide-react';
import AutocompleteSelect from './AutocompleteSelect';

function CreatePaymentModal({ api, onClose, onSave, errorMessage, onClearBackendError }) {
    const [formData, setFormData] = useState({
        type: 'espèces',
        customer_id: '',
        contrat_id: null,
        description: '',
        amount: '',
        date_payment: new Date().toISOString().slice(0, 10), // Date du jour
        category: 'travaux',
    });
    const [customers, setCustomers] = useState([]);
    const [validationError, setValidationError] = useState('');

    useEffect(() => {
        const fetchCustomers = async () => {
            const token = localStorage.getItem('authToken');
            try {
                const response = await fetch(`${api}/customers`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const result = await response.json();
                if (result.data) {
                    setCustomers(result.data);
                }
            } catch (error) {
                console.error(`Failed to fetch customers`, error);
            }
        };
        fetchCustomers();
    }, [api]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleAutocompleteChange = (name, value) => {
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setValidationError('');
        if (!formData.customer_id || !formData.amount || !formData.date_payment) {
            setValidationError('Veuillez remplir tous les champs obligatoires.');
            return;
        }
        if (parseFloat(formData.amount) <= 0) {
            setValidationError('Le montant doit être un nombre positif.');
            return;
        }
        onSave({
            ...formData,
            amount: parseFloat(formData.amount),
            customer_id: parseInt(formData.customer_id, 10),
            contrat_id: formData.contrat_id ? parseInt(formData.contrat_id, 10) : null,
        });
    };

    const handleCloseError = () => {
        setValidationError('');
        if (onClearBackendError) {
            onClearBackendError();
        }
    };

    return (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[90vh] flex flex-col overflow-hidden">
                <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">Créer un Paiement</h3>
                <div className="flex-grow overflow-y-auto p-6">
                    <form id="paymentForm" onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Client</label>
                            <AutocompleteSelect
                                options={customers}
                                value={formData.customer_id}
                                onChange={(value) => handleAutocompleteChange('customer_id', value)}
                                placeholder="Rechercher un client..."
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Montant</label>
                            <input
                                type="number"
                                name="amount"
                                value={formData.amount}
                                onChange={handleChange}
                                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Type de paiement</label>
                            <select
                                name="type"
                                value={formData.type}
                                onChange={handleChange}
                                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                            >
                                <option value="espèces">Espèces</option>
                                <option value="virement">Virement</option>
                                <option value="chèque">Chèque</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de paiement</label>
                            <input
                                type="date"
                                name="date_payment"
                                value={formData.date_payment}
                                onChange={handleChange}
                                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Description (optionnel)</label>
                            <textarea
                                name="description"
                                value={formData.description}
                                onChange={handleChange}
                                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Catégorie</label>
                            <select
                                name="category"
                                value={formData.category}
                                onChange={handleChange}
                                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                            >
                                <option value="travaux">Travaux</option>
                                <option value="services">Services</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div className="flex flex-col p-4 border-t dark:border-gray-700">
                    {(errorMessage || validationError) && (
                        <div className="relative mb-3 text-red-500 text-sm bg-red-100 border border-red-400 rounded p-3 pr-10">
                            <span>{validationError || errorMessage}</span>
                            <button onClick={handleCloseError} className="absolute top-1/2 right-2 transform -translate-y-1/2 text-red-500 hover:text-red-700" aria-label="Fermer la notification">
                                <X size={18} />
                            </button>
                        </div>
                    )}
                    <div className="flex justify-end space-x-3">
                        <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700">Annuler</button>
                        <button type="submit" form="paymentForm" className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default CreatePaymentModal;
