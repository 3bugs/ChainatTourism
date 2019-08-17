package th.ac.dusit.dbizcom.chainattourism.fragment;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Rect;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.widget.CircularProgressDrawable;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.DisplayMetrics;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.bumptech.glide.Glide;

import java.util.List;

import retrofit2.Call;
import retrofit2.Retrofit;
import th.ac.dusit.dbizcom.chainattourism.R;
import th.ac.dusit.dbizcom.chainattourism.model.Otop;
import th.ac.dusit.dbizcom.chainattourism.net.ApiClient;
import th.ac.dusit.dbizcom.chainattourism.net.GetOtopResponse;
import th.ac.dusit.dbizcom.chainattourism.net.MyRetrofitCallback;
import th.ac.dusit.dbizcom.chainattourism.net.WebServices;

public class OtopListFragment extends Fragment {

    private static final String ARG_DISTRICT_NAME = "district_name";

    private String mDistrictName;
    private List<Otop> mOtopList = null;

    private OtopListFragmentListener mListener;

    private View mProgressView;
    private RecyclerView mOtopListRecyclerView;

    public OtopListFragment() {
        // Required empty public constructor
    }

    public static OtopListFragment newInstance(String districtName) {
        OtopListFragment fragment = new OtopListFragment();
        Bundle args = new Bundle();
        args.putString(ARG_DISTRICT_NAME, districtName);
        fragment.setArguments(args);
        return fragment;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getArguments() != null) {
            mDistrictName = getArguments().getString(ARG_DISTRICT_NAME);
        }
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_otop_list, container, false);
    }

    @SuppressLint("SetTextI18n")
    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        TextView titleTextView = view.findViewById(R.id.title_text_view);
        titleTextView.setText("สินค้า OTOP");

        mOtopListRecyclerView = view.findViewById(R.id.otop_list_recycler_view);
        mProgressView = view.findViewById(R.id.progress_view);

        if (mOtopList == null) {
            doGetOtop();
        } else {
            setupRecyclerView();
        }
    }

    private void doGetOtop() {
        mProgressView.setVisibility(View.VISIBLE);

        Retrofit retrofit = ApiClient.getClient();
        WebServices services = retrofit.create(WebServices.class);

        Call<GetOtopResponse> call = services.getOtopByDistrict(mDistrictName);
        call.enqueue(new MyRetrofitCallback<>(
                getActivity(),
                null,
                mProgressView,
                new MyRetrofitCallback.MyRetrofitCallbackListener<GetOtopResponse>() {
                    @Override
                    public void onSuccess(GetOtopResponse responseBody) {
                        mOtopList = responseBody.otopList;
                        setupRecyclerView();
                    }

                    @Override
                    public void onError(String errorMessage) {

                    }
                }
        ));
    }

    private void setupRecyclerView() {
        if (getContext() != null) {
            OtopListAdapter adapter = new OtopListAdapter(
                    getContext(),
                    mOtopList,
                    mListener
            );
            mOtopListRecyclerView.setLayoutManager(new LinearLayoutManager(getContext()));
            mOtopListRecyclerView.addItemDecoration(new SpacingDecoration(getContext()));
            mOtopListRecyclerView.setAdapter(adapter);
        }
    }

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
        if (context instanceof OtopListFragmentListener) {
            mListener = (OtopListFragmentListener) context;
        } else {
            throw new RuntimeException(context.toString()
                    + " must implement OtopListFragmentListener");
        }
    }

    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    public interface OtopListFragmentListener {
        void onClickOtop(Otop otop);
    }

    private static class OtopListAdapter extends RecyclerView.Adapter<OtopListAdapter.ViewHolder> {

        private final Context mContext;
        private final List<Otop> mOtopList;
        private final OtopListFragmentListener mListener;

        OtopListAdapter(Context context, List<Otop> otopList, OtopListFragmentListener listener) {
            mContext = context;
            mOtopList = otopList;
            mListener = listener;
        }

        @NonNull
        @Override
        public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext()).inflate(
                    R.layout.item_otop, parent, false
            );
            return new ViewHolder(view);
        }

        @Override
        public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
            final Otop otop = mOtopList.get(position);

            holder.mOtop = otop;
            holder.mOtopNameTextView.setText(otop.name);
            holder.mVillageTextView.setText(otop.village);

            CircularProgressDrawable circularProgressDrawable = new CircularProgressDrawable(mContext);
            circularProgressDrawable.setStrokeWidth(5f);
            circularProgressDrawable.setCenterRadius(30f);
            circularProgressDrawable.start();

            Glide.with(mContext)
                    .load(ApiClient.IMAGE_BASE_URL.concat(otop.coverImage))
                    .placeholder(circularProgressDrawable)
                    .into(holder.mOtopImageView);
        }

        @Override
        public int getItemCount() {
            return mOtopList.size();
        }

        class ViewHolder extends RecyclerView.ViewHolder {

            private final View mRootView;
            private final TextView mOtopNameTextView;
            private final TextView mVillageTextView;
            private final ImageView mOtopImageView;

            private Otop mOtop;

            ViewHolder(View itemView) {
                super(itemView);

                mRootView = itemView;
                mOtopNameTextView = itemView.findViewById(R.id.otop_name_text_view);
                mVillageTextView = itemView.findViewById(R.id.village_text_view);
                mOtopImageView = itemView.findViewById(R.id.otop_image_view);

                mRootView.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        mListener.onClickOtop(mOtop);
                    }
                });
            }
        }
    }

    public class SpacingDecoration extends RecyclerView.ItemDecoration {

        private final static int MARGIN_TOP_IN_DP = 0;
        private final static int MARGIN_BOTTOM_IN_DP = 16;
        private final int mMarginTop, mMarginBottom;

        SpacingDecoration(@NonNull Context context) {
            DisplayMetrics metrics = context.getResources().getDisplayMetrics();
            mMarginTop = (int) TypedValue.applyDimension(
                    TypedValue.COMPLEX_UNIT_DIP,
                    MARGIN_TOP_IN_DP,
                    metrics
            );
            mMarginBottom = (int) TypedValue.applyDimension(
                    TypedValue.COMPLEX_UNIT_DIP,
                    MARGIN_BOTTOM_IN_DP,
                    metrics
            );
        }

        @Override
        public void getItemOffsets(@NonNull Rect outRect, @NonNull View view,
                                   @NonNull RecyclerView parent,
                                   @NonNull RecyclerView.State state) {
            final int itemPosition = parent.getChildAdapterPosition(view);
            if (itemPosition == RecyclerView.NO_POSITION) {
                return;
            }
            if (itemPosition == 0) {
                outRect.top = mMarginTop;
                //outRect.top = 0;
            }
            final RecyclerView.Adapter adapter = parent.getAdapter();
            if ((adapter != null) && (itemPosition == adapter.getItemCount() - 1)) {
                outRect.bottom = mMarginBottom;
            }
        }
    }
}
