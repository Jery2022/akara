// Suppliers Tab Component

function SuppliersTab({ suppliers, setSuppliers, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = suppliers.map((s) =>
      s.id === id ? { ...s, [field]: value } : s
    );
    setSuppliers(updated);

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((s) => s.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer ce fournisseur ?'))
      return;

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });
      setSuppliers(suppliers.filter((s) => s.id !== id));
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Fournisseurs
      </h2>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Nom</th>
              <th className="px-4 py-2 text-left">Contact</th>
              <th className="px-4 py-2 text-left">Téléphone</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {suppliers.map((supplier) => (
              <tr
                key={supplier.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">
                  <input
                    value={supplier.name}
                    onChange={(e) =>
                      handleUpdate(supplier.id, 'name', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={supplier.contact}
                    onChange={(e) =>
                      handleUpdate(supplier.id, 'contact', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={supplier.phone}
                    onChange={(e) =>
                      handleUpdate(supplier.id, 'phone', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(supplier.id)}
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
export default SuppliersTab;
