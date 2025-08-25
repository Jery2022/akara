import Card from './Card';
import ChartSection from './ChartSection';

// Dashboard Component
function DashboardTab({ stock, recettes, depenses }) {
  // Utilisez Array.isArray pour une vérification plus robuste
  const isStockArray = Array.isArray(stock);
  const isRecettesArray = Array.isArray(recettes);
  const isDepensesArray = Array.isArray(depenses);
  
  // Afficher un message de chargement si les données ne sont pas encore prêtes
  if (!isStockArray || !isRecettesArray || !isDepensesArray) {
    return (
      <div className="flex items-center justify-center p-8">
        <p className="text-gray-500 dark:text-gray-400">
          Chargement du tableau de bord...
        </p>
      </div>
    );
  }

  // Utiliser les tableaux vérifiés pour les calculs
  const lowStock = stock.filter(
    (item) =>
      typeof item.quantity === 'number' &&
      typeof item.min === 'number' &&
      item.quantity <= item.min
  );
  const totalRevenue = recettes.reduce(
    (sum, r) => sum + (parseFloat(r.total) || 0),
    0
  );
  const totalExpenses = depenses.reduce(
    (sum, d) => sum + (parseFloat(d.total) || 0),
    0
  );
  const profit = totalRevenue - totalExpenses;

  return (
    <div className="space-y-6">
      <h2 className="text-xl md:text-2xl font-semibold text-emerald-700 dark:text-emerald-400">
        Tableau de bord
      </h2>
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <Card
          title="Recettes totales"
          value={`${totalRevenue.toLocaleString()} FCFA`}
          color="green"
        />
        <Card
          title="Dépenses"
          value={`${totalExpenses.toLocaleString()} FCFA`}
          color="red"
        />
        <Card
          title="Bénéfice net"
          value={`${profit.toLocaleString()} FCFA`}
          color="emerald"
        />
      </div>

      <div className="bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-500 p-4 rounded shadow-inner">
        <h3 className="font-medium text-yellow-800 dark:text-yellow-200">
          Alertes Stocks Faibles
        </h3>
        <ul className="mt-2 space-y-1">
          {lowStock.length > 0 ? (
            lowStock.map((alert) => (
              <li
                key={alert.id}
                className="text-sm text-yellow-700 dark:text-yellow-300"
              >
                {alert.produit_nom}: seulement {alert.quantity} unités restantes
              </li>
            ))
          ) : (
            <li>Aucun stock faible.</li>
          )}
        </ul>
      </div>

      <ChartSection recettes={recettes} depenses={depenses} />
    </div>
  );
}
export default DashboardTab;
