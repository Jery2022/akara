// factures Tab Component

function FacturesTab({ factures, setFactures, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = factures.map((f) =>
      f.id === id ? { ...f, [field]: value } : f
    );
    setFactures(updated);
    try {
      await fetch(`${api}&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updated.find((f) => f.id === id)),
      });
    } catch (err) {
      alert('Erreur lors de la mise à jour.');
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Voulez-vous vraiment supprimer cette facture ?'))
      return;
    const updated = factures.filter((f) => f.id !== id);
    setFactures(updated);
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
      <h2 className="text-lg font-bold mb-4">Factures</h2>
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
          {factures.map((facture) => (
            <tr key={facture.id}>
              <td className="py-2 px-4 border-b">{facture.id}</td>
              <td className="py-2 px-4 border-b">{facture.client}</td>
              <td className="py-2 px-4 border-b">{facture.montant}</td>
              <td className="py-2 px-4 border-b">{facture.date}</td>
              <td className="py-2 px-4 border-b">
                <button
                  onClick={() =>
                    handleUpdate(
                      facture.id,
                      'montant',
                      prompt('Nouveau montant:', facture.montant)
                    )
                  }
                  className="text-blue-600 hover:underline"
                >
                  Modifier
                </button>
                <button
                  onClick={() => handleDelete(facture.id)}
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
export default FacturesTab;
