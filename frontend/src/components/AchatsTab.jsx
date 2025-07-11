function AchatsTab({ achats, setAchats, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = achats.map((a) =>
      a.id === id ? { ...a, [field]: value } : a
    );
    setAchats(updated);
    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((a) => a.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };
  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer cet achat ?')) return;
    const updated = achats.filter((a) => a.id !== id);
    setAchats(updated);
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
        Achats
      </h2>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Fournisseur</th>
              <th className="px-4 py-2 text-left">Montant</th>
              <th className="px-4 py-2 text-left">Date</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {achats.map((achat) => (
              <tr
                key={achat.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">
                  <input
                    value={achat.fournisseur}
                    onChange={(e) =>
                      handleUpdate(achat.id, 'fournisseur', e.target.value)
                    }
                    className="w-full bg-transparent border-b border-gray-300 focus:border-blue-500 focus:outline-none"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="number"
                    value={achat.montant}
                    onChange={(e) =>
                      handleUpdate(achat.id, 'montant', e.target.value)
                    }
                    className="w-full bg-transparent border-b border-gray-300 focus:border-blue-500 focus:outline-none"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="date"
                    value={achat.date}
                    onChange={(e) =>
                      handleUpdate(achat.id, 'date', e.target.value)
                    }
                    className="w-full bg-transparent border-b border-gray-300 focus:border-blue-500 focus:outline-none"
                  />
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(achat.id)}
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

export default AchatsTab;
