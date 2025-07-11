// Ventes Tab Component
import React from 'react';

function VentesTab({ ventes, setVentes, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = ventes.map((v) =>
      v.id === id ? { ...v, [field]: value } : v
    );
    setVentes(updated);

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((v) => v.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };
  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer cette vente ?')) return;
    const updated = ventes.filter((v) => v.id !== id);
    setVentes(updated);
    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };
  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Ventes
      </h2>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Client</th>
              <th className="px-4 py-2 text-left">Montant</th>
              <th className="px-4 py-2 text-left">Date</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {ventes.map((vente) => (
              <tr
                key={vente.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">
                  <input
                    value={vente.client}
                    onChange={(e) =>
                      handleUpdate(vente.id, 'client', e.target.value)
                    }
                    className="w-full bg-transparent border-b border-gray-300 focus:border-blue-500 focus:outline-none"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="number"
                    value={vente.montant}
                    onChange={(e) =>
                      handleUpdate(vente.id, 'montant', e.target.value)
                    }
                    className="w-full bg-transparent border-b border-gray-300 focus:border-blue-500 focus:outline-none"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="date"
                    value={vente.date}
                    onChange={(e) =>
                      handleUpdate(vente.id, 'date', e.target.value)
                    }
                    className="w-full bg-transparent border-b border-gray-300 focus:border-blue-500 focus:outline-none"
                  />
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(vente.id)}
                    className="text-red-600 hover:text-red-800"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export default VentesTab;
