//Recettes Tab Component

function RecettesTab({ recettes, setRecettes, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = recettes.map((r) =>
      r.id === id ? { ...r, [field]: value } : r
    );
    setRecettes(updated);

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((r) => r.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer cette recette ?'))
      return;

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });
      setRecettes(recettes.filter((r) => r.id !== id));
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Recettes
      </h2>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Date</th>
              <th className="px-4 py-2 text-left">Description</th>
              <th className="px-4 py-2 text-left">Montant</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {recettes.map((recette) => (
              <tr
                key={recette.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">
                  <input
                    type="date"
                    value={recette.date}
                    onChange={(e) =>
                      handleUpdate(recette.id, 'date', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="text"
                    value={recette.description}
                    onChange={(e) =>
                      handleUpdate(recette.id, 'description', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="number"
                    value={recette.montant}
                    onChange={(e) =>
                      handleUpdate(recette.id, 'montant', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(recette.id)}
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
export default RecettesTab;
