import axios from 'axios';

export const listUnread = async (page = 1) => {
  const response = await axios.get(route('notifications.unread', { type: 'task' }) + `?page=${page}`);
  return response.data;
};
