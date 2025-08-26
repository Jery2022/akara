// frontend/src/components/utils/CreatePaymentModal.jsx

import React, { useState } from 'react';
//import {  X } from 'lucide-react';

function CreatePaymentModal ({ onClose, onSave, customers, employees }) {
    const [formData, setFormData] = useState({
        type: 'espèces',
        customer_id: '',
        user_id: '',
        contrat_id: null,
        description: '',
        amount: '',
        date_payment: new Date().toISOString().slice(0, 10), // Date du jour
        category: 'travaux',
    });
    const [error, setError] = useState('');

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (!formData.customer_id || !formData.user_id || !formData.amount || !formData.date_payment) {
            setError('Veuillez remplir tous les champs obligatoires.');
            return;
        }
        onSave({
            ...formData,
            amount: parseFloat(formData.amount), // Convertir en nombre
            customer_id: parseInt(formData.customer_id, 10),
            user_id: parseInt(formData.user_id, 10),
            contrat_id: formData.contrat_id ? parseInt(formData.contrat_id, 10) : null,
        });
    };

    return (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[500px] flex flex-col overflow-hidden">
                <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">Créer un Paiement</h3>
                <div className="flex-grow overflow-y-auto p-6">
                    {error && <div className="text-red-500 mb-4">{error}</div>}
                    <form id="paymentForm" onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Client</label>
                            <select
                                name="customer_id"
                                value={formData.customer_id}
                                onChange={handleChange}
                                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                            >
                                <option value="">Sélectionner un client</option>
                                {customers?.map(customer => (
                                    <option key={customer.id} value={customer.id}>
                                        {customer.firstName} {customer.lastName}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Employé</label>
                            <select
                                name="user_id"
                                value={formData.user_id}
                                onChange={handleChange}
                                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                            >
                                <option value="">Sélectionner un employé</option>
                                {employees?.map(employee => (
                                    <option key={employee.id} value={employee.id}>
                                        {employee.firstName} {employee.lastName}
                                    </option>
                                ))}
                            </select>
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
                <div className="flex justify-end space-x-3 p-4 border-t dark:border-gray-700">
                    <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700">Annuler</button>
                    <button type="submit" form="paymentForm" className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800">Enregistrer</button>
                </div>
            </div>
        </div>
    );
};

export default CreatePaymentModal
