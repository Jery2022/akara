function PaymentsTab({ payments, setPayments, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = payments.map((p) =>
      p.id === id ? { ...p, [field]: value } : p
    );
    setPayments(updated);

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((p) => p.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer ce paiement ?')) return;

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });
      setPayments(payments.filter((p) => p.id !== id));
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Paiements
      </h2>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Date</th>
              <th className="px-4 py-2 text-left">Description</th>
              <th className="px-4 py-2 text-left">Montant</th>
              <th className="px-4 py-2 text-left">Type</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {payments.map((tx) => (
              <tr
                key={tx.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">
                  <input
                    type="date"
                    value={tx.date}
                    onChange={(e) =>
                      handleUpdate(tx.id, 'date', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={tx.description}
                    onChange={(e) =>
                      handleUpdate(tx.id, 'description', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={tx.amount}
                    onChange={(e) =>
                      handleUpdate(tx.id, 'amount', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <select
                    value={tx.type}
                    onChange={(e) =>
                      handleUpdate(tx.id, 'type', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  >
                    <option value="income">Entrée</option>
                    <option value="expense">Sortie</option>
                  </select>
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(tx.id)}
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
export default PaymentsTab;
