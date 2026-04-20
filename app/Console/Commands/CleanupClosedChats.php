<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ChatSession;
use Carbon\Carbon;

class CleanupClosedChats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-chats';

    /**
     * The message that will be displayed in the console.
     *
     * @var string
     */
    protected $description = 'Xóa các phiên chat đã đóng sau 15 ngày';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = ChatSession::where('status', 'closed')
            ->where('updated_at', '<', Carbon::now()->subDays(15))
            ->delete();

        $this->info("Đã xóa {$count} phiên chat cũ.");
    }
}
