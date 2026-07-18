export default function DashboardHeader() {
  return (
    <div className="bg-white dark:bg-slate-900 border-b border-border px-8 py-4 flex justify-between items-center">
      <div className="flex items-center gap-4 flex-1">
        <input
          type="text"
          placeholder="Search properties, tenants, payments..."
          className="input-field max-w-md"
        />
      </div>

      <div className="flex items-center gap-4">
        <button className="p-2 hover:bg-background rounded-lg transition-colors">
          🔔
        </button>
        <button className="p-2 hover:bg-background rounded-lg transition-colors">
          ⚙️
        </button>
        <div className="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-bold cursor-pointer">
          A
        </div>
      </div>
    </div>
  );
}
