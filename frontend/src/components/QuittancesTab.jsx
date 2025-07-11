// Quitances Tab component
import React from 'react';
import { useToast } from './ToastProvider';

function QuittancesTab({ quittances, setQuittances, api }) {
  const { addToast } = useToast();
  const handleUpdate = async (id, field, value) => {
    const updated = quittances.map((f) =>
      f.id === id ? { ...f, [field]: value } : f
    );
    setQuittances(updated);
    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((f) => f.id === id)),
      });
    } catch (err) {
      addToast('Erreur lors de la mise à jour.', 'danger');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer cette quittance ?'))
      return;
    const updated = quittances.filter((f) => f.id !== id);
    setQuittances(updated);
    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });
    } catch (err) {
      addToast('Erreur lors de la suppression.', 'danger');
      console.error(err);
    }
  };
  return (
    <div>
      <h2 className="text-lg font-bold mb-4">Quittances</h2>
      <table className="min-w-full bg-white border border-gray-200">
        <thead>
          <tr>
            <th className="py-2 px-4 border-b">ID</th>
            <th className="py-2 px-4 border-b">Client</th>
            <th className="py-2 px-4 border-b">Montant</th>
            <th className="py-2 px-4 border-b">Date</th>
            <th className="py-2 px-4 border-b">Actions</th>
          </tr>
        </thead>
        <tbody>
          {quittances.map((quittance) => (
            <tr key={quittance.id}>
              <td className="py-2 px-4 border-b">{quittance.id}</td>
              <td className="py-2 px-4 border-b">{quittance.client}</td>
              <td className="py-2 px-4 border-b">{quittance.montant}</td>
              <td className="py-2 px-4 border-b">{quittance.date}</td>
              <td className="py-2 px-4 border-b">
                <button
                  onClick={() =>
                    handleUpdate(
                      quittance.id,
                      'montant',
                      prompt('Nouveau montant:', quittance.montant)
                    )
                  }
                  className="text-blue-600 hover:underline"
                >
                  Modifier
                </button>
                <button
                  onClick={() => handleDelete(quittance.id)}
                  className="text-red-600 hover:underline ml-4"
                >
                  Supprimer
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default QuittancesTab;
