// Employees Tab Component

function EmployeesTab({ employees, setEmployees, api }) {
  const handleUpdate = async (id, field, value) => {
    const updated = employees.map((e) =>
      e.id === id ? { ...e, [field]: value } : e
    );
    setEmployees(updated);

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
    if (!window.confirm('Voulez-vous vraiment supprimer cet employé ?')) return;

    try {
      await fetch(`${api}&id=${id}`, {
        method: 'DELETE',
      });
      setEmployees(employees.filter((e) => e.id !== id));
    } catch (err) {
      alert('Erreur lors de la suppression.');
      console.error(err);
    }
  };

  return (
    <div>
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400 mb-4">
        Employés
      </h2>
      <div className="overflow-x-auto">
        <table className="w-full table-auto text-sm">
          <thead className="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th className="px-4 py-2 text-left">Nom</th>
              <th className="px-4 py-2 text-left">Rôle</th>
              <th className="px-4 py-2 text-left">Salaire</th>
              <th className="px-4 py-2 text-left">Dernier paiement</th>
              <th className="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {employees.map((emp) => (
              <tr
                key={emp.id}
                className="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750"
              >
                <td className="px-4 py-2">
                  <input
                    value={emp.name}
                    onChange={(e) =>
                      handleUpdate(emp.id, 'name', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={emp.role}
                    onChange={(e) =>
                      handleUpdate(emp.id, 'role', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    value={emp.salary}
                    onChange={(e) =>
                      handleUpdate(emp.id, 'salary', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <input
                    type="date"
                    value={emp.last_payment}
                    onChange={(e) =>
                      handleUpdate(emp.id, 'last_payment', e.target.value)
                    }
                    className="w-full p-1 border rounded"
                  />
                </td>
                <td className="px-4 py-2">
                  <button
                    onClick={() => handleDelete(emp.id)}
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
export default EmployeesTab;
