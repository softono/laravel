<x-ui.badge :variant="$status === \App\Constants\UserStatus::ACTIVE ? 'success' : 'destructive'">{{ ucfirst($status) }}</x-ui.badge>
