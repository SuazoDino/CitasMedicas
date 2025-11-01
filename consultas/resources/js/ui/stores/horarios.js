import { reactive } from 'vue'

const state = reactive({
  lists: {},
  versions: {},
})

export function useHorariosStore () {
  const setHorarios = (medicoId, horarios) => {
    if (!medicoId) return
    state.lists[medicoId] = Array.isArray(horarios)
      ? horarios.map(h => ({ ...h }))
      : []
  }

  const getHorarios = (medicoId) => {
    return medicoId ? (state.lists[medicoId] ?? []) : []
  }

  const notifyChange = (medicoId) => {
    if (!medicoId) return
    state.versions[medicoId] = (state.versions[medicoId] ?? 0) + 1
  }

  const versionOf = (medicoId) => {
    if (!medicoId) return 0
    return state.versions[medicoId] ?? 0
  }

  return {
    state,
    setHorarios,
    getHorarios,
    notifyChange,
    versionOf,
  }
}