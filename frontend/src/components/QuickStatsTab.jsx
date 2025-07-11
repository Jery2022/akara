import Card from './Card';

// QuickStats Component
function QuickStats({ payments }) {
  const totalIncome = payments
    .filter((p) => p.type === 'income')
    .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
  const totalExpenses = payments
    .filter((p) => p.type === 'expense')
    .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
  const balance = totalIncome - totalExpenses;

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
      <Card title="Revenus Totals" value={totalIncome} color="green" />
      <Card title="Dépenses Totals" value={totalExpenses} color="red" />
      <Card
        title="Solde"
        value={balance}
        color={balance >= 0 ? 'emerald' : 'red'}
      />
    </div>
  );
}
export default QuickStats;
