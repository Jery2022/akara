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

    const handleSave = () => {
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
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center">
            <div className="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                <h3 className="text-xl font-bold mb-4">Créer un Paiement</h3>
                {error && <div className="text-red-500 mb-4">{error}</div>}
                <div className="space-y-4">
                    <div>
                        <label className="block text-gray-700">Client</label>
                        <select
                            name="customer_id"
                            value={formData.customer_id}
                            onChange={handleChange}
                            className="w-full p-2 border border-gray-300 rounded-md"
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
                        <label className="block text-gray-700">Employé</label>
                        <select
                            name="user_id"
                            value={formData.user_id}
                            onChange={handleChange}
                            className="w-full p-2 border border-gray-300 rounded-md"
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
                        <label className="block text-gray-700">Montant</label>
                        <input
                            type="number"
                            name="amount"
                            value={formData.amount}
                            onChange={handleChange}
                            className="w-full p-2 border border-gray-300 rounded-md"
                        />
                    </div>
                    <div>
                        <label className="block text-gray-700">Type de paiement</label>
                        <select
                            name="type"
                            value={formData.type}
                            onChange={handleChange}
                            className="w-full p-2 border border-gray-300 rounded-md"
                        >
                            <option value="espèces">Espèces</option>
                            <option value="virement">Virement</option>
                            <option value="chèque">Chèque</option>
                        </select>
                    </div>
                    <div>
                        <label className="block text-gray-700">Date de paiement</label>
                        <input
                            type="date"
                            name="date_payment"
                            value={formData.date_payment}
                            onChange={handleChange}
                            className="w-full p-2 border border-gray-300 rounded-md"
                        />
                    </div>
                    <div>
                        <label className="block text-gray-700">Description (optionnel)</label>
                        <textarea
                            name="description"
                            value={formData.description}
                            onChange={handleChange}
                            className="w-full p-2 border border-gray-300 rounded-md"
                        />
                    </div>
                    <div>
                        <label className="block text-gray-700">Catégorie</label>
                        <select
                            name="category"
                            value={formData.category}
                            onChange={handleChange}
                            className="w-full p-2 border border-gray-300 rounded-md"
                        >
                            <option value="travaux">Travaux</option>
                            <option value="services">Services</option>
                        </select>
                    </div>
                </div>
                <div className="mt-6 flex justify-end space-x-2">
                    <button onClick={onClose} className="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-colors">Annuler</button>
                    <button onClick={handleSave} className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">Enregistrer</button>
                </div>
            </div>
        </div>
    );
};

export default CreatePaymentModal