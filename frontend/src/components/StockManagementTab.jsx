import React from 'react';
import { useState } from 'react';

function StockManagementTab({ items, setItems, api }) {
  const [search, setSearch] = useState('');
  const [newItem, setNewItem] = useState({
    name: '',
    quantity: '',
    unit: '',
    min: '',
  });

  const filteredItems = items.filter((item) =>
    item.name?.toLowerCase().includes(search.toLowerCase())
  );

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!newItem.name || !newItem.quantity || !newItem.unit || !newItem.min)
      return;

    try {
      const res = await fetch(api, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newItem),
      });

      if (!res.ok) throw new Error('Erreur lors de l’ajout');

      const data = await res.json();
      setItems([...items, { ...newItem, id: data.id }]);
      setNewItem({ name: '', quantity: '', unit: '', min: '' });
    } catch (err) {
      alert('Impossible d’ajouter cet article.');
      console.error(err);
    }
  };

  const handleUpdate = async (id, field, value) => {
    const updated = items.map((item) =>
      item.id === id ? { ...item, [field]: value } : item
    );
    setItems(updated);

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((item) => item.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer cet élément ?')) return;

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });

      setItems(items.filter((item) => item.id !== id));
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Gestion des Stocks
      </h2>
      <div className="mb-4">
        <input
          type="text"
          placeholder="Rechercher..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-full p-2 border rounded"
        />
      </div>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">ID</th>
              <th className="px-4 py-2 text-left">Nom</th>
              <th className="px-4 py-2 text-left">Quantité</th>
              <th className="px-4 py-2 text-left">Unité</th>
              <th className="px-4 py-2 text-left">Seuil minimum</th>
              <th className="px-4 py-2 text-left">Statut</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {filteredItems.map((item) => (
              <tr
                key={item.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">{item.id}</td>
                <td className="px-4 py-2">
                  <input
                    value={item.name}
                    onChange={(e) =>
                      handleUpdate(item.id, 'name', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="number"
                    value={item.quantity}
                    onChange={(e) =>
                      handleUpdate(
                        item.id,
                        'quantity',
                        parseInt(e.target.value)
                      )
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={item.unit}
                    onChange={(e) =>
                      handleUpdate(item.id, 'unit', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="number"
                    value={item.min}
                    onChange={(e) =>
                      handleUpdate(item.id, 'min', parseInt(e.target.value))
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  {item.quantity <= item.min ? (
                    <span className="text-red-600 font-medium">En rupture</span>
                  ) : (
                    <span className="text-green-600 font-medium">OK</span>
                  )}
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(item.id)}
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

      <div className="mt-4">
        <h3 className="font-medium mb-2">Ajouter un article</h3>
        <form onSubmit={handleSubmit} className="flex gap-2 flex-wrap">
          <input
            placeholder="Nom"
            value={newItem.name}
            onChange={(e) => setNewItem({ ...newItem, name: e.target.value })}
            className="p-2 border rounded"
          />
          <input
            placeholder="Qté"
            type="number"
            value={newItem.quantity}
            onChange={(e) =>
              setNewItem({ ...newItem, quantity: e.target.value })
            }
            className="p-2 border rounded"
          />
          <input
            placeholder="Unité"
            value={newItem.unit}
            onChange={(e) => setNewItem({ ...newItem, unit: e.target.value })}
            className="p-2 border rounded"
          />
          <input
            placeholder="Min"
            type="number"
            value={newItem.min}
            onChange={(e) => setNewItem({ ...newItem, min: e.target.value })}
            className="p-2 border rounded"
          />
          <button
            type="submit"
            className="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded"
          >
            Ajouter
          </button>
        </form>
      </div>
    </div>
  );
}
export default StockManagementTab;
