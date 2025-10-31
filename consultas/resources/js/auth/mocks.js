// resources/js/auth/mocks.js
const LS = {
  get(k, d) { try { return JSON.parse(localStorage.getItem(k)) ?? d } catch { return d } },
  set(k, v) { localStorage.setItem(k, JSON.stringify(v)) }
};

const KEYS = {
  USERS: 'mr.mock.users',
  CITAS: 'mr.mock.citas',
  ESPS:  'mr.mock.especialidades',
  MEDS:  'mr.mock.medicos',
  HOURS: 'mr.mock.horarios', // horarios base por médico (slots 30min)
};

export function mockSetup() {
  if (!LS.get(KEYS.USERS)) {
    LS.set(KEYS.USERS, [
      { id: 1, name: 'Dr. Juan Pérez',  role: 'medico',   verif_status: 'pendiente' },
      { id: 2, name: 'Carlos Martínez', role: 'paciente', verif_status: 'ok' },
    ]);
  }
  if (!LS.get(KEYS.ESPS)) {
    LS.set(KEYS.ESPS, [
      { id: 10, nombre: 'Medicina General' },
      { id: 11, nombre: 'Dermatología' },
      { id: 12, nombre: 'Cardiología' },
      { id: 13, nombre: 'Neurología' },
    ]);
  }
  if (!LS.get(KEYS.MEDS)) {
    LS.set(KEYS.MEDS, [
      { id: 1, user_id: 1, nombre: 'Dr. Juan Pérez', especialidad_id: 10 },
      { id: 3, user_id: 1, nombre: 'Dra. Ana Torres', especialidad_id: 11 },
    ]);
  }
  if (!LS.get(KEYS.HOURS)) {
    // Horarios: por cada médico, rango mañana y tarde, slots de 30min
    LS.set(KEYS.HOURS, {
      "1": [ { dia:[1,2,3,4,5], inicio:"09:00", fin:"12:00" }, { dia:[1,3,5], inicio:"15:00", fin:"17:30" } ], // L-V / L,M,V
      "3": [ { dia:[2,4], inicio:"10:00", fin:"13:00" } ], // Ma,J
    });
  }
  if (!LS.get(KEYS.CITAS)) {
    // Demo: 3 citas hoy
    const hoy = new Date(); hoy.setSeconds(0,0);
    const iso = (h,m)=>{ const d=new Date(hoy); d.setHours(h,m,0,0); return d.toISOString() };
    LS.set(KEYS.CITAS, [
      { id: 101, medico_id:1, paciente_id:2, starts_at: iso(9,  0), ends_at: iso(9,30),  estado:'pendiente'  },
      { id: 102, medico_id:1, paciente_id:2, starts_at: iso(11, 0), ends_at: iso(11,30), estado:'confirmada' },
      { id: 103, medico_id:1, paciente_id:2, starts_at: iso(16,30), ends_at: iso(17,0),  estado:'pendiente'  },
    ]);
  }
}

function nowISO() { return new Date().toISOString() }
function fmtHora(iso){ return new Date(iso).toTimeString().slice(0,5) }
function ymd(d){ const y=d.getFullYear(), m=String(d.getMonth()+1).padStart(2,'0'), dd=String(d.getDate()).padStart(2,'0'); return `${y}-${m}-${dd}` }
function addMinIso(iso, min){ const d=new Date(iso); d.setMinutes(d.getMinutes()+min); return d.toISOString() }
function weekday(d){ return d.getDay() } // 0 Dom .. 6 Sáb

function nextId(key){
  const all = LS.get(key, []); return all.length ? Math.max(...all.map(x=>x.id))+1 : 1;
}

function slotsByHorario(medico_id, desde, hasta){
  const HRS = LS.get(KEYS.HOURS, {});
  const cfg = HRS[String(medico_id)] ?? [];
  const out = [];
  const d0 = new Date(desde), d1 = new Date(hasta); d0.setHours(0,0,0,0); d1.setHours(23,59,59,999);
  for (let d=new Date(d0); d <= d1; d = new Date(d.getTime()+86400000)){
    const wd = weekday(d); // 0-6
    const match = cfg.filter(c => c.dia.includes(wd===0?7:wd)); // si guardas 1-7 L-D; arriba use 1=L..7=D
    // si en HOURS usamos 1..7, convertimos wd: 1..6,0->7
    for(const c of match){
      const [h0,m0] = c.inicio.split(':').map(Number);
      const [h1,m1] = c.fin.split(':').map(Number);
      const base = new Date(d); base.setHours(h0,m0,0,0);
      const end  = new Date(d); end.setHours(h1,m1,0,0);
      for (let t=new Date(base); t<end; t = new Date(t.getTime()+30*60000)){
        const start = t.toISOString();
        const fin   = addMinIso(start, 30);
        out.push({ start, end: fin });
      }
    }
  }
  return out;
}

function getTokenUser() {
  const tok = localStorage.getItem('token') || localStorage.getItem('auth_token');
  const users = LS.get(KEYS.USERS, []);
  if (tok?.toLowerCase()?.includes('medico'))   return users.find(u=>u.role==='medico')   ?? users[0];
  if (tok?.toLowerCase()?.includes('paciente')) return users.find(u=>u.role==='paciente') ?? users[1];
  return users.find(u=>u.role==='paciente') ?? users[0];
}

export async function mockApi(method, url, { params, data } = {}) {
  const users = LS.get(KEYS.USERS, []);
  const citas = LS.get(KEYS.CITAS, []);
  const esps  = LS.get(KEYS.ESPS, []);
  const meds  = LS.get(KEYS.MEDS, []);
  const me    = getTokenUser();

  // ---- AUTH ----
  if (method === 'GET' && url === 'auth/me') {
    return { user: { id: me.id, name: me.name, verif_status: me.verif_status }, roles: [me.role] };
  }

  // ---- PÚBLICO ----
  if (method === 'GET' && url === 'public/especialidades') {
    return esps;
  }
  if (method === 'GET' && url.startsWith('public/medicos')) {
    let list = meds;
    const esp = Number(params?.especialidad_id);
    if (esp) list = list.filter(m=>m.especialidad_id===esp);
    return list;
  }
  if (method === 'GET' && url.match(/^public\/medicos\/\d+\/slots$/)) {
    const medico_id = +url.split('/')[2];
    const desde = params?.desde || ymd(new Date());
    const hasta = params?.hasta || ymd(new Date(Date.now()+7*86400000));
    const slots = slotsByHorario(medico_id, new Date(desde), new Date(hasta));
    // marca ocupados por citas
    const booked = LS.get(KEYS.CITAS, []).filter(c => c.medico_id===medico_id).map(c=>c.starts_at);
    return slots.map(s => ({ ...s, taken: booked.includes(s.start) }));
  }

  // ---- PACIENTE ----
  if (method === 'GET' && url === 'paciente/citas/proximas') {
    const mine = citas
      .filter(c => c.paciente_id === (users.find(u=>u.role==='paciente')?.id ?? 2))
      .filter(c => c.starts_at >= nowISO())
      .sort((a,b)=> a.starts_at.localeCompare(b.starts_at))
      .slice(0,10)
      .map(c => ({
        id: c.id,
        medico: users.find(u=>u.id=== (meds.find(m=>m.id===c.medico_id)?.user_id ?? 1))?.name ?? 'Médico',
        especialidad: esps.find(e=>e.id === (meds.find(m=>m.id===c.medico_id)?.especialidad_id))?.nombre ?? null,
        fecha: new Date(c.starts_at).toLocaleDateString(),
        hora:  fmtHora(c.starts_at),
        lugar: null,
        estado: c.estado
      }));
    return mine;
  }

  if (method === 'POST' && url === 'paciente/citas') {
    const paciente_id = users.find(u=>u.role==='paciente')?.id ?? 2;
    const { medico_id, starts_at, ends_at } = data || {};
    if (!medico_id || !starts_at) return { ok:false, message:'Datos incompletos' };
    // Validación simple: slot libre
    const booked = citas.some(c => c.medico_id===medico_id && c.starts_at===starts_at);
    if (booked) return { ok:false, message:'Ese horario ya fue reservado' };
    const id = nextId(KEYS.CITAS);
    const row = { id, medico_id, paciente_id, starts_at, ends_at: ends_at || addMinIso(starts_at,30), estado:'pendiente' };
    const all = LS.get(KEYS.CITAS, []); all.push(row); LS.set(KEYS.CITAS, all);
    return { ok:true, id };
  }

  // ---- MÉDICO ----
  if (method === 'GET' && url === 'medico/citas') {
    const day = (params?.date) ? new Date(params.date) : new Date();
    const onlyDay = ymd(day);
    const mine = citas
      .filter(c => c.medico_id === (meds[0]?.id ?? 1))
      .filter(c => c.starts_at.slice(0,10) === onlyDay)
      .sort((a,b)=> a.starts_at.localeCompare(b.starts_at))
      .map(c => ({
        id: c.id,
        starts_at: c.starts_at,
        paciente: users.find(u=>u.id===c.paciente_id)?.name ?? 'Paciente',
        estado: c.estado
      }));
    return mine;
  }

  if (method === 'POST' && url.match(/^medico\/citas\/\d+\/confirmar$/)) {
    const id = +url.match(/\d+/)[0];
    const all = LS.get(KEYS.CITAS, []);
    const i = all.findIndex(c=>c.id===id); if (i>=0) { all[i].estado='confirmada'; LS.set(KEYS.CITAS, all); }
    return { ok:true };
  }
  if (method === 'POST' && url.match(/^medico\/citas\/\d+\/completar$/)) {
    const id = +url.match(/\d+/)[0];
    const all = LS.get(KEYS.CITAS, []);
    const i = all.findIndex(c=>c.id===id); if (i>=0) { all[i].estado='completada'; LS.set(KEYS.CITAS, all); }
    return { ok:true };
  }
  if (method === 'POST' && url.match(/^medico\/citas\/\d+\/cancelar$/)) {
    const id = +url.match(/\d+/)[0];
    const all = LS.get(KEYS.CITAS, []);
    const i = all.findIndex(c=>c.id===id); if (i>=0) { all[i].estado='cancelada'; LS.set(KEYS.CITAS, all); }
    return { ok:true };
  }

  return { ok:true };
}
