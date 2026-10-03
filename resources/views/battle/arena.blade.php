@extends('layouts.app')

@section('title', 'ساحة المعركة المباشرة 1v1')

@section('content')
<div style="max-width: 850px; margin: auto;">
    <!-- Versus Scoreboard -->
    <div class="card" style="padding: 20px 28px; margin-bottom: 24px; background: linear-gradient(135deg, rgba(16,24,43,0.95), rgba(7,10,20,0.95)); border-color: rgba(239, 68, 68, 0.4);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <!-- Player Side -->
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 0 20px rgba(0,217,255,0.4);">
                    👤
                </div>
                <div>
                    <div style="font-weight: 900; font-size: 16px;">{{ $user->name }}</div>
                    <div style="font-size: 12px; color: #4ade80; font-weight: 700;">(أنت)</div>
                </div>
                <div id="playerScore" style="font-size: 36px; font-weight: 900; color: #4ade80; margin-right: 14px;">
                    0
                </div>
            </div>

            <!-- VS Badge -->
            <div style="text-align: center;">
                <div style="font-size: 26px; font-weight: 900; color: #ef4444; text-shadow: 0 0 15px rgba(239,68,68,0.7); letter-spacing: 2px;">
                    VS
                </div>
                <div id="roundBadge" style="font-size: 11px; color: var(--muted); font-weight: 800; background: var(--card2); padding: 2px 8px; border-radius: 999px; margin-top: 4px;">
                    الجولة 1 من 3
                </div>
            </div>

            <!-- AI Rival Side -->
            <div style="display: flex; align-items: center; gap: 14px; flex-direction: row-reverse;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, #ef4444, #f59e0b); display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 0 20px rgba(239,68,68,0.4);">
                    🤖
                </div>
                <div style="text-align: left;">
                    <div style="font-weight: 900; font-size: 16px;">AI Rival</div>
                    <div style="font-size: 12px; color: #f87171; font-weight: 700;">(منافس ذكي)</div>
                </div>
                <div id="aiScore" style="font-size: 36px; font-weight: 900; color: #f87171; margin-left: 14px;">
                    0
                </div>
            </div>
        </div>

        <!-- 10-Second Timer Bar -->
        <div style="margin-top: 18px; height: 8px; background: var(--line); border-radius: 999px; overflow: hidden;">
            <div id="timerBar" style="height: 100%; width: 100%; background: linear-gradient(90deg, #ef4444, #f59e0b, #4ade80); transition: width 0.1s linear;"></div>
        </div>
    </div>

    <!-- Active Question Arena -->
    <div class="card" id="arenaCard" style="padding: 35px 28px; text-align: center;">
        <div id="questionNumber" style="font-size: 13px; font-weight: 800; color: var(--secondary); margin-bottom: 8px;">
            السؤال الأول
        </div>
        <h2 id="questionText" style="font-size: 22px; font-weight: 800; line-height: 1.6; margin-bottom: 28px;">
            جاري تحميل السؤال...
        </h2>

        <!-- 4 Interactive Option Cards -->
        <div id="optionsGrid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; max-width: 650px; margin: auto;">
            <!-- Rendered via JS -->
        </div>

        <!-- Status Message -->
        <div id="statusMsg" style="margin-top: 20px; font-size: 15px; font-weight: 800; min-height: 24px;"></div>
    </div>

    <!-- Victory Modal (Hidden by default) -->
    <div id="victoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: blur(10px); z-index: 200; place-items: center; padding: 20px;">
        <div class="card" style="width: min(500px, 100%); text-align: center; padding: 40px 28px; border: 2px solid var(--primary); box-shadow: 0 0 50px rgba(109,93,252,0.4);">
            <div id="modalIcon" style="font-size: 60px; margin-bottom: 12px;">🏆</div>
            <h2 id="modalTitle" style="font-size: 28px; font-weight: 900; margin-bottom: 8px;">انتصار ساحق!</h2>
            <p id="modalDesc" style="color: var(--muted); font-size: 15px; margin-bottom: 24px; line-height: 1.6;"></p>

            <div class="grid2" style="margin-bottom: 24px;">
                <div class="card-inner">
                    <div style="font-size: 12px; color: var(--muted);">نقاطك</div>
                    <div id="finalPlayerScore" style="font-size: 26px; font-weight: 900; color: #4ade80;">0</div>
                </div>
                <div class="card-inner">
                    <div style="font-size: 12px; color: var(--muted);">نقاط الخصم الآلي</div>
                    <div id="finalAiScore" style="font-size: 26px; font-weight: 900; color: #f87171;">0</div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="{{ route('battle.start') }}" class="btn danger">معركة جديدة ⚔️</a>
                <a href="{{ route('dashboard') }}" class="btn primary">الرئيسية 🚀</a>
            </div>
        </div>
    </div>
</div>

<script>
    const battleData = @json($battleData);
    const battleId = {{ $battle->id }};
    const csrfToken = '{{ csrf_token() }}';

    let currentRound = 0;
    let playerScore = 0;
    let aiScore = 0;
    let roundTimer = null;
    let roundStartTime = 0;
    let hasAnsweredRound = false;

    function startRound() {
        if (currentRound >= battleData.length) {
            finishBattle();
            return;
        }

        hasAnsweredRound = false;
        const q = battleData[currentRound];

        document.getElementById('roundBadge').innerText = `الجولة ${currentRound + 1} من ${battleData.length}`;
        document.getElementById('questionNumber').innerText = `الجولة ${currentRound + 1}`;
        document.getElementById('questionText').innerText = q.text;
        document.getElementById('statusMsg').innerText = '';

        const grid = document.getElementById('optionsGrid');
        grid.innerHTML = '';

        q.options.forEach((optText, optIdx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn';
            btn.style.padding = '18px 14px';
            btn.style.fontSize = '16px';
            btn.style.fontWeight = '800';
            btn.style.textAlign = 'center';
            btn.style.border = '1px solid var(--line)';
            btn.innerText = optText;
            btn.onclick = () => handlePlayerChoice(optIdx, btn);
            grid.appendChild(btn);
        });

        // 10-Second Timer
        roundStartTime = Date.now();
        const duration = 10000;
        const timerBar = document.getElementById('timerBar');

        clearInterval(roundTimer);
        roundTimer = setInterval(() => {
            const elapsed = Date.now() - roundStartTime;
            const remaining = Math.max(0, duration - elapsed);
            const percent = (remaining / duration) * 100;
            timerBar.style.width = percent + '%';

            if (remaining <= 0) {
                clearInterval(roundTimer);
                if (!hasAnsweredRound) {
                    handleTimeOut();
                }
            }
        }, 100);

        // Schedule simulated AI answer between 2.5s and 5.5s
        const aiDelay = Math.random() * 3000 + 2500;
        setTimeout(() => {
            if (currentRound < battleData.length) {
                // AI has 75% accuracy
                const isAiCorrect = Math.random() < 0.75;
                if (isAiCorrect) {
                    const aiPts = Math.max(20, Math.floor(100 - (aiDelay / 1000) * 8));
                    aiScore += aiPts;
                    document.getElementById('aiScore').innerText = aiScore;
                }
            }
        }, aiDelay);
    }

    function handlePlayerChoice(chosenIndex, btn) {
        if (hasAnsweredRound) return;
        hasAnsweredRound = true;
        clearInterval(roundTimer);

        const q = battleData[currentRound];
        const isCorrect = (chosenIndex === q.correctIndex);
        const responseTime = Date.now() - roundStartTime;

        // Disable all buttons
        const allBtns = document.getElementById('optionsGrid').children;
        for (let b of allBtns) {
            b.disabled = true;
            b.style.cursor = 'default';
        }

        if (isCorrect) {
            btn.style.background = 'var(--success)';
            btn.style.color = '#fff';
            btn.style.borderColor = 'var(--success)';
            const speedBonus = Math.max(10, Math.floor((10000 - responseTime) / 100));
            const pts = 50 + speedBonus;
            playerScore += pts;
            document.getElementById('playerScore').innerText = playerScore;
            document.getElementById('statusMsg').innerHTML = `<span style="color: #4ade80;">إجابة صحيحة وسريعة! (+${pts} نقطة) ⚡</span>`;
        } else {
            btn.style.background = 'var(--danger)';
            btn.style.color = '#fff';
            btn.style.borderColor = 'var(--danger)';
            // Highlight the correct one
            if (allBtns[q.correctIndex]) {
                allBtns[q.correctIndex].style.background = 'var(--success)';
                allBtns[q.correctIndex].style.color = '#fff';
            }
            document.getElementById('statusMsg').innerHTML = `<span style="color: #f87171;">إجابة غير صحيحة! لم تحصل على نقاط في هذه الجولة.</span>`;
        }

        setTimeout(() => {
            currentRound++;
            startRound();
        }, 1600);
    }

    function handleTimeOut() {
        hasAnsweredRound = true;
        document.getElementById('statusMsg').innerHTML = `<span style="color: #fbbf24;">انتهى الوقت المخصص للجولة!</span>`;
        setTimeout(() => {
            currentRound++;
            startRound();
        }, 1200);
    }

    function finishBattle() {
        clearInterval(roundTimer);

        fetch(`{{ url('/battle') }}/${battleId}/finish`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                student_score: playerScore,
                ai_score: aiScore,
            })
        }).catch(err => console.error(err));

        const isWin = playerScore > aiScore;
        const isTie = playerScore === aiScore;

        document.getElementById('modalIcon').innerText = isWin ? '🏆' : (isTie ? '🤝' : '🤖');
        document.getElementById('modalTitle').innerText = isWin ? 'انتصار ساحق ومبهر! 🎉' : (isTie ? 'تعادل شريف ومثير!' : 'فاز الخصم الذكي هذه المرة!');
        document.getElementById('modalDesc').innerText = isWin
            ? 'أحسنت صنعاً! لقد تفوقت في السرعة والدقة وحصلت على +50 XP في رصيدك.'
            : 'أداء رائع وتجربة تنافسية قوية. يمكنك خوض معركة جديدة لصقل مهارتك!';
        document.getElementById('finalPlayerScore').innerText = playerScore;
        document.getElementById('finalAiScore').innerText = aiScore;

        const modal = document.getElementById('victoryModal');
        modal.style.display = 'grid';
    }

    // Launch battle on load
    startRound();
</script>
@endsection
