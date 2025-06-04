# Chat Room Deletion Feature Guide

## Overview
The chat room deletion feature allows authorized users (room admins and super admins) to permanently delete chat rooms with proper confirmation dialogs and real-time notifications.

## Features

### 🔐 **Authorization**
- **Room Admins**: Can delete rooms they created or have admin role in
- **Super Admins**: Can delete any room (role_users_id = 1)
- **Regular Members**: Cannot delete rooms

### 🎯 **User Interface**
1. **Manage Button**: Click the "Manage" button (⚙️) in the chat header when viewing a room
2. **Room Management Modal**: Shows room information, members list, and admin actions
3. **Delete Confirmation**: Two-step confirmation process to prevent accidental deletions

### ⚡ **Real-time Features**
- **Instant Notifications**: All room members get notified when a room is deleted
- **Auto-removal**: Room disappears from sidebar immediately for all users
- **Active Room Handling**: If users are currently in the deleted room, they're automatically redirected

## How to Use

### For Room Admins/Super Admins:

1. **Access Room Management**:
   - Select a chat room from the sidebar
   - Click the "Manage" (⚙️) button in the chat header
   - The Room Management modal will open

2. **View Room Details**:
   - See room information (name, description, type, creator)
   - View all room members with their roles
   - Check your permissions

3. **Delete Room** (if authorized):
   - Scroll to the "Danger Zone" section (red border)
   - Read the warning about permanent deletion
   - Click "Delete Room" button
   - Confirm deletion in the popup modal
   - Click "Delete Room" again to confirm

4. **Cancel Deletion**:
   - Click "Cancel" in the confirmation modal
   - Click "Close" in the room management modal
   - Nothing will be changed

### For Regular Users:

- **Receive Notifications**: Get real-time notifications when rooms are deleted
- **Automatic Redirect**: If you're in a deleted room, you'll be automatically moved out
- **No Delete Access**: The delete option won't appear for regular members

## Technical Implementation

### Backend (Laravel)
- **Route**: `DELETE /chat/room/{roomId}/delete`
- **Controller**: `ChatController@deleteRoom`
- **Authorization**: Checks user role and room membership
- **Cascade Deletion**: Automatically removes messages, files, and memberships
- **File Cleanup**: Deletes uploaded files from storage
- **Broadcasting**: Sends real-time notifications to all room members

### Frontend (Vue.js)
- **Modal System**: Two-modal confirmation process
- **Real-time Updates**: WebSocket listeners for room deletion events
- **State Management**: Automatic cleanup of local data
- **User Feedback**: Toast notifications and visual feedback

### Database
- **Soft Deletes**: Rooms are soft-deleted for potential recovery
- **Cascade Constraints**: Related data is automatically cleaned up
- **File Storage**: Physical files are removed from storage

## Security Features

1. **Double Authorization**: Checks both user role and room membership
2. **Confirmation Process**: Two-step confirmation prevents accidents
3. **Audit Trail**: Logs who deleted the room and when
4. **Permission Validation**: Server-side validation of delete permissions

## Error Handling

- **Unauthorized Access**: Returns 403 error for non-authorized users
- **Room Not Found**: Returns 404 error for non-existent rooms
- **Server Errors**: Graceful error handling with user-friendly messages
- **Network Issues**: Retry mechanisms and error notifications

## Real-time Notifications

When a room is deleted:
1. **Broadcast Event**: `RoomDeleted` event is sent to all room members
2. **Notification Message**: "Room '[Room Name]' was deleted by [Username]"
3. **Sound Alert**: Notification sound plays for all users
4. **Visual Update**: Room disappears from sidebar immediately
5. **Auto-redirect**: Users in the deleted room are moved to welcome screen

## Best Practices

### For Administrators:
- **Backup Important Data**: Export important messages before deletion
- **Notify Members**: Inform room members before deleting active rooms
- **Consider Archiving**: Use room deactivation instead of deletion when possible

### For Developers:
- **Test Thoroughly**: Test deletion with multiple users and scenarios
- **Monitor Performance**: Watch for performance impact with large rooms
- **Backup Strategy**: Implement regular database backups
- **Audit Logging**: Consider adding detailed audit logs for deletions

## Troubleshooting

### Common Issues:
1. **Delete Button Not Visible**: Check user permissions and room membership
2. **Deletion Fails**: Verify server permissions and database constraints
3. **Real-time Not Working**: Check WebSocket connection and broadcasting setup
4. **Files Not Deleted**: Verify storage permissions and file paths

### Debug Steps:
1. Check browser console for JavaScript errors
2. Verify WebSocket connection status
3. Check Laravel logs for server errors
4. Confirm database constraints and relationships

## Future Enhancements

- **Room Archiving**: Option to archive instead of delete
- **Bulk Operations**: Delete multiple rooms at once
- **Recovery System**: Restore deleted rooms within time limit
- **Export Feature**: Export room data before deletion
- **Advanced Permissions**: More granular permission system
