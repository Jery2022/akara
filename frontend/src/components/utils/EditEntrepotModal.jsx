import React, { useState, useEffect } from 'react';
import { X } from 'lucide-react';

function EditEntrepotModal({ entrepotToEdit, onClose, onSave, errorMessage, onClearBackendError }) {
  const [formState, setFormState] = useState({
    id: '',
    name: '',
    adresse: '',
    responsable: '',
    email: '',
    telephone: '',
    capacity: 0,
    quality_stockage: 'bonne',
    black_list: 'non'
  });
  const [validationError, setValidationError] = useState('');

  useEffect(() => {
    if (entrepotToEdit) {
      setFormState({
        id: entrepotToEdit.id,
        name: entrepotToEdit.name || '',
        adresse: entrepotToEdit.adresse || '',
        responsable: entrepotToEdit.responsable || '',
        email: entrepotToEdit.email || '',
        telephone: entrepotToEdit.telephone || '',
        capacity: entrepotToEdit.capacity || 0,
        quality_stockage: entrepotToEdit.quality_stockage || 'bonne',
        black_list: entrepotToEdit.black_list || 'non'
      });
    }
  }, [entrepotToEdit]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormState(prevState => ({ ...prevState, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setValidationError('');

    if (!formState.name || !formState.adresse || !formState.responsable) {
      setValidationError('Le nom, l\'adresse et le responsable sont obligatoires.');
      return;
    }

    onSave(formState);
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
        <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">Modifier l'entrepôt</h3>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="editEntrepotForm" onSubmit={handleSubmit} className="space-y-4">
            <input type="hidden" name="id" value={formState.id} />
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom de l'entrepôt</label>
              <input type="text" name="name" value={formState.name} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Adresse</label>
              <input type="text" name="adresse" value={formState.adresse} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Responsable</label>
              <input type="text" name="responsable" value={formState.responsable} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
              <input type="email" name="email" value={formState.email} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Téléphone</label>
              <input type="text" name="telephone" value={formState.telephone} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacité</label>
              <input type="number" name="capacity" value={formState.capacity} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Qualité du stockage</label>
              <select name="quality_stockage" value={formState.quality_stockage} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                <option value="bonne">Bonne</option>
                <option value="moyenne">Moyenne</option>
                <option value="mauvaise">Mauvaise</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Blacklisté</label>
              <select name="black_list" value={formState.black_list} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                <option value="non">Non</option>
                <option value="oui">Oui</option>
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
            <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700">
              Annuler
            </button>
            <button type="submit" form="editEntrepotForm" className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800">
              Enregistrer les modifications
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default EditEntrepotModal;
