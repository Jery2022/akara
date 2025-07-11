// Entrepots Tab Component

function EntrepotsTab({ entrepots, setEntrepots, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = entrepots.map((e) =>
      e.id === id ? { ...e, [field]: value } : e
    );
    setEntrepots(updated);

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((e) => e.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer cet entrepôt ?'))
      return;

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });
      setEntrepots(entrepots.filter((e) => e.id !== id));
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Entrepôts
      </h2>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Nom</th>
              <th className="px-4 py-2 text-left">Emplacement</th>
              <th className="px-4 py-2 text-left">Capacité</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {entrepots.map((entrepot) => (
              <tr
                key={entrepot.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">
                  <input
                    value={entrepot.name}
                    onChange={(e) =>
                      handleUpdate(entrepot.id, 'name', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={entrepot.location}
                    onChange={(e) =>
                      handleUpdate(entrepot.id, 'location', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={entrepot.capacity}
                    onChange={(e) =>
                      handleUpdate(entrepot.id, 'capacity', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(entrepot.id)}
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
export default EntrepotsTab;
