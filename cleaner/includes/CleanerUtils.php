<?php

class CleanerUtils {

	public static function canCleanRoom($roomId, $leaves, $roomChanges) {
		logDebug("Checking if we can clean room: $roomId");
		$leavesCnt = 0;
		$rcCnt = 0;
		foreach($leaves as $oneLeave) {
			$leftRoomId = (is_null($oneLeave['new_room_id']) ? $oneLeave['room_id'] : $oneLeave['new_room_id']);	
			if($leftRoomId == $roomId and $oneLeave['checked_in'] == 1) {
				logDebug("The leaving booking: " . $oneLeave['bid'] . " is still checked in. Cannot clean it yet.");
				return false;
			}
			if($leftRoomId  == $roomId) { $leavesCnt += 1; }
		}
		logDebug("All the $leavesCnt leavers are already checked out");
		foreach($roomChanges as $oneChange) {
			$changedRoomId = (is_null($oneRc['yesterday_new_room_id']) ? $oneRc['room_id'] : $oneRc['yesterday_new_room_id']);
			if($changedRoomId  == $roomId and !is_null($oneChange['today_new_room_id']) and is_null($oneChange['enter_room_time'])) {
				logDebug("The room change goes to the 'changed room' but has not yet entered it (still in the normal room)");
				return false;
			}
			if($changedRoomId  == $roomId and !is_null($oneChange['yesterday_new_room_id']) and is_null($oneChange['left_room_time'])) {
				logDebug("The room change goes to the 'normal room' but has not yet left the changed room");
				return false;
			}
			if($changedRoomId  == $roomId) { $rcCnt += 1; }
		}
		logDebug("All the $rcCnt room changers changed already");
		return true;
	}

	
	public static function translate($text) {
		if($text == 'ROOM') return 'szoba';
		if($text == 'BATHROOM') return 'fürdöszoba';
		if($text == 'FINISH_ROOM') return 'Szoba kész';
		if($text == 'ENTER_BATHROOM') return 'Fürdöszobában van';
		if($text == 'ENTER_ROOM') return 'Szobában van';
		if($text == 'LEAVE_BATHROOM') return 'Fürdöszoba takarítható';
		if($text == 'LEAVE_ROOM') return 'Szoba takarítható';
		if($text == 'FINISH_BATHROOM') return 'Fürdöszoba kész';
		if($text == 'CONFIRM_FINISH_BATHROOM') return 'Fürdöszoba jováhagyva';
		if($text == 'CONFIRM_FINISH_ROOM') return 'Szoba jováhagyva';
		if($text == 'REJECT_FINISH_BATHROOM') return 'Fürdöszoba elutasítva';
		if($text == 'REJECT_FINISH_ROOM') return 'Szoba elutasítva';
	}
	
}

?>
