import api from './apiClient.js'

export default {
  getAll(params) {
    return api.get('/deals', { params })
  },
  get(id) {
    return api.get(`/deals/${id}`)
  },
  create(data) {
    return api.post('/deals', data)
  },
  update(id, data) {
    return api.put(`/deals/${id}`, data)
  },
  delete(id) {
    return api.delete(`/deals/${id}`)
  },
  updateStatus(id, status) {
    return api.patch(`/deals/${id}/status`, { status })
  },
  getComments(dealId) {
    return api.get(`/deals/${dealId}/comments`)
  },
  addComment(dealId, content) {
    return api.post(`/deals/${dealId}/comments`, { content })
  },
}
