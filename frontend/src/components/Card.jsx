// Card Component

const bgColors = {
  green: 'bg-green-100 dark:bg-green-900',
  red: 'bg-red-100 dark:bg-red-900',
  emerald: 'bg-emerald-100 dark:bg-emerald-900',
  gray: 'bg-gray-100 dark:bg-gray-900',
};

function Card({ title, value, color = 'gray' }) {
  return (
    <div className={`${bgColors[color] || bgColors.gray} p-4 rounded shadow`}>
      <h3 className="text-sm text-gray-600 dark:text-gray-400">{title}</h3>
      <p className="text-2xl font-bold mt-1 text-gray-800 dark:text-white">
        {value}
      </p>
    </div>
  );
}

export default Card;
