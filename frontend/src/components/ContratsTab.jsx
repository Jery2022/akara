import React, { useState, useEffect, useCallback } from 'react';
import EditContractModal from './EditContractModal';
import CreateContractModal from './CreateContractModal'; // Importez le nouveau composant de modale

// Composant de l'onglet Contrats
function ContratsTab({ contracts, setContrats, api }) {
  const token = localStorage.getItem('authToken');

  // État pour gérer le chargement des données (lecture)
  const [loading, setLoading] = useState(true);
  // État pour gérer les erreurs de chargement
  const [error, setError] = useState(null);

  // --- Nouveaux états pour les modales ---
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [currentContract, setCurrentContract] = useState(null); // Contrat à modifier

  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false); // État pour la modale de création

  // Fonction pour charger les contrats (Read)
  const fetchContrats = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      console.log("Tentative de chargement depuis l'URL:", api);
      const response = await fetch(api, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }
      const data = await response.json();
      setContrats(data);
    } catch (err) {
      console.error('Erreur lors du chargement des contrats :', err);
      setError('Impossible de charger les contrats. Veuillez réessayer.');
    } finally {
      setLoading(false);
    }
  }, [api, token, setContrats]);

  // Charger les contrats au montage du composant
  useEffect(() => {
    fetchContrats();
  }, [fetchContrats]);

  // Fonctions pour la modale d'édition
  const openEditModal = (contract) => {
    setCurrentContract(contract);
    setIsEditModalOpen(true);
  };

  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setCurrentContract(null);
  };

  // Fonctions pour la modale de création
  const openCreateModal = () => {
    setIsCreateModalOpen(true);
  };

  const closeCreateModal = () => {
    setIsCreateModalOpen(false);
  };

  // Gère la soumission du formulaire de modification depuis la modale
  const handleUpdateContract = async (updatedContract) => {
    try {
      const response = await fetch(`${api}/${updatedContract.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(updatedContract),
      });

      if (!response.ok) {
        throw new Error(
          `Erreur lors de la mise à jour: ${response.statusText}`
        );
      }

      setContrats((prevContracts) =>
        prevContracts.map((c) =>
          c.id === updatedContract.id ? updatedContract : c
        )
      );
      closeEditModal();
      alert('Contrat mis à jour avec succès !');
    } catch (err) {
      alert('Erreur lors de la mise à jour du contrat. Veuillez réessayer.');
      console.error(err);
      fetchContrats();
    }
  };

  // Gère la création d'un nouveau contrat (appelé par la modale de création)
  const handleCreateContract = async (newContractData) => {
    try {
      const response = await fetch(api, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(newContractData),
      });

      if (!response.ok) {
        throw new Error(`Erreur lors de la création: ${response.statusText}`);
      }

      const createdContract = await response.json();
      setContrats([...contracts, createdContract]);
      closeCreateModal(); // Ferme la modale après la création réussie
      alert('Contrat créé avec succès !');
    } catch (err) {
      alert('Erreur lors de la création du contrat.');
      console.error(err);
    }
  };

  // Gère la suppression d'un contrat (Delete)
  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer ce contrat ?')) return;

    try {
      const response = await fetch(`${api}/${id}`, {
        method: 'DELETE',
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (!response.ok) {
        throw new Error(
          `Erreur lors de la suppression: ${response.statusText}`
        );
      }
      setContrats(contracts.filter((c) => c.id !== id));
      alert('Contrat supprimé avec succès !');
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
      fetchContrats();
    }
  };

  if (loading) {
    return (
      <div className="text-center text-gray-600 dark:text-gray-400">
        Chargement des contrats...
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center text-red-600 dark:text-red-400">{error}</div>
    );
  }

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Gestion des Contrats
      </h2>

      {/* Bouton pour ouvrir le formulaire de création en modale */}
      <div className="mb-8 text-right">
        <button
          onClick={openCreateModal}
          className="px-4 py-2 bg-emerald-600 text-white font-semibold rounded-md shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:bg-emerald-700 dark:hover:bg-emerald-800"
        >
          Ajouter un nouveau contrat
        </button>
      </div>

      {/* Tableau d'affichage des contrats */}
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Libellé</th>
              <th className="px-4 py-2 text-left">Objet</th>
              <th className="px-4 py-2 text-left">Date début</th>
              <th className="px-4 py-2 text-left">Date fin</th>
              <th className="px-4 py-2 text-left">Date signature</th>
              <th className="px-4 py-2 text-left">Type</th>
              <th className="px-4 py-2 text-left">Montant</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {contracts.length === 0 ? (
              <tr>
                <td
                  colSpan="8"
                  className="px-4 py-4 text-center text-gray-500 dark:text-gray-400"
                >
                  Aucun contrat trouvé.
                </td>
              </tr>
            ) : (
              contracts.map((contract) => (
                <tr
                  key={contract.id}
                  className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
                >
                  <td className="px-4 py-2">{contract.name}</td>
                  <td className="px-4 py-2">{contract.objet}</td>
                  <td className="px-4 py-2">{contract.start_date}</td>
                  <td className="px-4 py-2">{contract.end_date}</td>
                  <td className="px-4 py-2">{contract.sign_date}</td>
                  <td className="px-4 py-2">{contract.type}</td>
                  <td className="px-4 py-2">{contract.amount}</td>
                  <td className="px-4 py-2">
                    <button
                      onClick={() => openEditModal(contract)}
                      className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-500 mr-2"
                    >
                      Modifier
                    </button>
                    <button
                      onClick={() => handleDelete(contract.id)}
                      className="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-500"
                    >
                      Supprimer
                    </button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Modale de modification */}
      {isEditModalOpen && currentContract && (
        <EditContractModal
          contract={currentContract}
          onClose={closeEditModal}
          onSave={handleUpdateContract}
        />
      )}

      {/* Modale de création */}
      {isCreateModalOpen && (
        <CreateContractModal
          onClose={closeCreateModal}
          onSave={handleCreateContract} // Passe la fonction de création
        />
      )}
    </div>
  );
}

export default ContratsTab;
