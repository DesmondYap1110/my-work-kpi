{{--
    A staff member's dashboard: where they stand and what is on their plate.
    Every tile links somewhere they are allowed to go - their own scorecard and
    their own tasks. Included by dashboard/index.blade.php.
--}}
    <div class="row">
        <div class="col-lg-4">
            <a id="ft-box" href="{{ route('my.kpi') }}">
                <div id="ft-icon-div">
                    <div id="ft-icon">
                        <i class="bx ri-bar-chart-2-line"></i>
                    </div>
                </div>
                <div id="ft-info-div">
                    <p id="ft-title">My KPI Score</p>
                    {{-- No score yet is not a score of nothing: a member with
                         no objectives and no delivered work reads "-". --}}
                    <p id="ft-p">{{ $myScore === null ? '-' : $myScore.' %' }}</p>
                </div>
            </a>
        </div>
        <div class="col-lg-4">
            <a id="ft-box" href="{{ route('my.tasks') }}">
                <div id="ft-icon-div">
                    <div id="ft-icon">
                        <i class="bx ri-list-check-2"></i>
                    </div>
                </div>
                <div id="ft-info-div">
                    <p id="ft-title">Open Tasks</p>
                    <p id="ft-p">{{ $openTaskCount }}</p>
                </div>
            </a>
        </div>
        <div class="col-lg-4">
            <a id="ft-box" href="{{ route('my.tasks') }}">
                <div id="ft-icon-div">
                    <div id="ft-icon">
                        <i class="bx ri-alarm-warning-line"></i>
                    </div>
                </div>
                <div id="ft-info-div">
                    <p id="ft-title">Overdue Tasks</p>
                    <p id="ft-p">{{ $overdueTaskCount }}</p>
                </div>
            </a>
        </div>
        <div class="col-lg-4">
            <a id="ft-box" href="{{ route('my.tasks') }}">
                <div id="ft-icon-div">
                    <div id="ft-icon">
                        <i class="bx ri-check-double-line"></i>
                    </div>
                </div>
                <div id="ft-info-div">
                    <p id="ft-title">Completed Tasks</p>
                    <p id="ft-p">{{ $doneTaskCount }}</p>
                </div>
            </a>
        </div>
    </div>
