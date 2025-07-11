// Settings Component

function Settings({ toggleTheme, isDarkMode }) {
  return (
    <div className="p-4">
      <h2 className="text-lg font-bold mb-2">Paramètres</h2>
      <div className="flex items-center">
        <label className="mr-2">Mode Sombre</label>
        <input type="checkbox" checked={isDarkMode} onChange={toggleTheme} />
      </div>
    </div>
  );
}
export default Settings;
