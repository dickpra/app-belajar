<!-- resources/views/student/navbar-bawah.blade.php -->
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t-4 border-slate-200 rounded-t-[2rem] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.1)] z-[60] md:hidden transform transition-transform duration-300">
    <div class="pb-safe">
        <div class="flex justify-around items-end p-2 px-4 bg-white rounded-t-[2rem]">
            
            <!-- MENU 1: PETA -->
            <a href="{{ route('student.dashboard') }}" class="flex flex-col items-center gap-1 group transform transition-transform active:scale-90 {{ request()->routeIs('student.dashboard') ? 'opacity-100' : 'opacity-60 hover:opacity-100' }}">
                <div class="p-2 transition-all {{ request()->routeIs('student.dashboard') ? 'relative bg-blue-100 p-2.5 rounded-2xl border-[3px] border-blue-200 shadow-sm -mt-4' : '' }}">
                    <span class="text-2xl block {{ request()->routeIs('student.dashboard') ? 'drop-shadow-sm' : 'grayscale group-hover:grayscale-0' }}">🗺️</span>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest mt-0 {{ request()->routeIs('student.dashboard') ? 'text-blue-600 mt-1' : 'text-slate-400 group-hover:text-blue-500' }}">Peta</span>
            </a>

            <!-- MENU 2: RAPORKU -->
            <a href="{{ route('student.raporku') }}" class="flex flex-col items-center gap-1 group transform transition-transform active:scale-90 {{ request()->routeIs('student.raporku') ? 'opacity-100' : 'opacity-60 hover:opacity-100' }}">
                <div class="p-2 transition-all {{ request()->routeIs('student.raporku') ? 'relative bg-emerald-100 p-2.5 rounded-2xl border-[3px] border-emerald-200 shadow-sm -mt-4' : '' }}">
                    <span class="text-2xl block {{ request()->routeIs('student.raporku') ? 'drop-shadow-sm' : 'grayscale group-hover:grayscale-0' }}">📚</span>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest mt-0 {{ request()->routeIs('student.raporku') ? 'text-emerald-600 mt-1' : 'text-slate-400 group-hover:text-emerald-500' }}">Raporku</span>
            </a>

            <!-- MENU 3: PANDUAN -->
            <a href="{{ route('student.panduan') }}" class="flex flex-col items-center gap-1 group transform transition-transform active:scale-90 {{ request()->routeIs('student.panduan') ? 'opacity-100' : 'opacity-60 hover:opacity-100' }}">
                <div class="p-2 transition-all {{ request()->routeIs('student.panduan') ? 'relative bg-amber-100 p-2.5 rounded-2xl border-[3px] border-amber-200 shadow-sm -mt-4' : '' }}">
                    <span class="text-2xl block {{ request()->routeIs('student.panduan') ? 'drop-shadow-sm' : 'grayscale group-hover:grayscale-0' }}">💡</span>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest mt-0 {{ request()->routeIs('student.panduan') ? 'text-amber-600 mt-1' : 'text-slate-400 group-hover:text-amber-500' }}">Panduan</span>
            </a>

            <!-- MENU 4: PROFIL -->
            <a href="{{ route('student.profil') }}" class="flex flex-col items-center gap-1 group transform transition-transform active:scale-90 {{ request()->routeIs('student.profil') ? 'opacity-100' : 'opacity-60 hover:opacity-100' }}">
                <div class="p-2 transition-all {{ request()->routeIs('student.profil') ? 'relative bg-purple-100 p-2.5 rounded-2xl border-[3px] border-purple-200 shadow-sm -mt-4' : '' }}">
                    <span class="text-2xl block {{ request()->routeIs('student.profil') ? 'drop-shadow-sm' : 'grayscale group-hover:grayscale-0' }}">👤</span>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest mt-0 {{ request()->routeIs('student.profil') ? 'text-purple-600 mt-1' : 'text-slate-400 group-hover:text-purple-500' }}">Profil</span>
            </a>

        </div>
    </div>
</nav>