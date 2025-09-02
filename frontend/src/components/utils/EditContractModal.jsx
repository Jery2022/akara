import React, { useState, useEffect } from 'react';
import { X } from 'lucide-react';

function EditContractModal({ api, onClose, onSave, contractToEdit, errorMessage, onClearBackendError }) {
  const [formState, setFormState] = useState({
    id: '',
    name: '',
    objet: '',
    date_debut: '',
    date_fin: '',
    date_signature: '',
    type: '',
    montant: '',
    status: '',
    signataire: '',
    fichier_contrat: '',
  });
  const [selectedFile, setSelectedFile] = useState(null);
  const [validationError, setValidationError] = useState('');

  useEffect(() => {
    if (contractToEdit) {
      setFormState({
        id: contractToEdit.id,
        name: contractToEdit.name || '',
        objet: contractToEdit.objet || '',
        date_debut: contractToEdit.date_debut || '',
        date_fin: contractToEdit.date_fin || '',
        date_signature: contractToEdit.date_signature || '',
        type: contractToEdit.type || '',
        montant: contractToEdit.montant || '',
        status: contractToEdit.status || 'en cours',
        signataire: contractToEdit.signataire || '',
        fichier_contrat: contractToEdit.fichier_contrat || '',
      });
    }
  }, [contractToEdit]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormState((prev) => ({ ...prev, [name]: value }));
  };

  const handleFileChange = (e) => {
    setSelectedFile(e.target.files[0]);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setValidationError('');

    const requiredFields = ['name', 'objet', 'date_debut', 'date_fin', 'montant', 'date_signature', 'type'];
    for (const field of requiredFields) {
      const value = formState[field];
      if (!value || (typeof value === 'string' && !value.trim())) {
        setValidationError(`Le champ '${field}' est obligatoire.`);
        return;
      }
    }
    if (parseFloat(formState.montant) < 0) {
      setValidationError('Le montant ne peut pas être négatif.');
      return;
    }

    let filePath = formState.fichier_contrat;
    if (selectedFile) {
      const formData = new FormData();
      formData.append('file', selectedFile);

      try {
        const token = localStorage.getItem('authToken');
        const response = await fetch(`http://localhost:8000/backend/api/upload`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
          body: formData,
        });

        const result = await response.json();
        if (response.ok) {
          filePath = result.data.filePath;
        } else {
          setValidationError(result.message || 'Erreur lors du téléversement du fichier.');
          return;
        }
      } catch (error) {
        setValidationError('Erreur réseau lors du téléversement du fichier.');
        return;
      }
    }

    onSave({ ...formState, fichier_contrat: filePath });
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
        <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">Modifier le contrat</h3>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="editContractForm" onSubmit={handleSubmit} className="space-y-4">
            <input type="hidden" name="id" value={formState.id} />
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom / Réf</label>
              <input type="text" name="name" value={formState.name} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Objet</label>
              <input type="text" name="objet" value={formState.objet} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de début</label>
                <input type="date" name="date_debut" value={formState.date_debut} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de fin</label>
                <input type="date" name="date_fin" value={formState.date_fin} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de signature</label>
              <input type="date" name="date_signature" value={formState.date_signature} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Montant</label>
              <input type="number" step="0.01" name="montant" value={formState.montant} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                <select name="type" value={formState.type} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                  <option value="">Sélectionner un type</option>
                  <option value="client">Client</option>
                  <option value="fournisseur">Fournisseur</option>
                  <option value="employe">Employé</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Statut</label>
                <select name="status" value={formState.status} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                  <option value="en cours">En cours</option>
                  <option value="terminé">Terminé</option>
                  <option value="annulé">Annulé</option>
                </select>
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Signataire</label>
              <input type="text" name="signataire" value={formState.signataire} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Fichier du contrat</label>
              <input type="file" name="fichier_contrat" onChange={handleFileChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
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
            <button type="submit" form="editContractForm" className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800">
              Enregistrer les modifications
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default EditContractModal;
